#!/usr/bin/env python

import sys
#import math

from scipy import spatial # Cosine similarity calculation

#import average_vector
import filter_vocab_words
import string_util


def getInternalSet( arr_words, model):
    """
    Get IntS (internal, kernel) words for words in the array arr_words.
        Word w belong to IntS iff
        (1) set S = S1 and S2 (subsets of S),
        (2) subsets S1 and S2 are not empty,
        (3) dist(S1, S2) > dist(S1 + w, S2),
        (4) dist(S1, S2) > dist(S1    , S2 + w), 
        (where "dist" is Cosine similarity between average vectors of S1 and S2 - subsets of vectors),
        i.e. the word w makes close any subsets S1 and S2 after adding.
    
    Parameters
    ----------
    arr_words : array of Strings
        Array of source words, for example a synonym set or a sentence' words.
    model : object
        Word2Vec model.
        
    Returns
    -------
    array of Strings
        Internal subset for the source list of words.
        Empty array if there are no such words.
    """
        
    result_int_s = []
    DEBUG_PRINT = False #True

    arr_words = filter_vocab_words.filterVocabWords( arr_words, model.vocab )
    #print string_util.joinUtf8( ",", arr_words )                              # after filter, now there are only words with vectors
    
    len_words = len(arr_words)
    #print "len_words = {}".format( len_words )
    
    if len_words < 3:
        return []       # it is possible calculate IntS only when there are >= 3 words

#    current_synset  = lib.synset.Synset()
#    current_synset.headword = arr_words[0] # let's first element in synset is a headword (? target word)
#    current_synset.line     = line

#    syn_rank       = dict()  # integer
#    syn_centrality = dict()  # float
#    syn_internal   = dict()  # boolean (true for IntS, for synonyms, which always make subsets more nearer)

    # let's take all subsets for every 'out' element
    i=0
    while (i < len_words):
        gr = arr_words[:]
        # extract the element 'out' which is under consideration
        test_word = gr.pop(i)
        #test_word_counter_int   = 0
        #test_word_counter_float = 0

        sim12_greater_sim0_always = True
        for j in range(0, len(gr)):
            for l in range(j, len(gr)-1):
                gr1 = gr[j:l+1]
                gr2 = gr[0:j]+gr[l+1:len(gr)]
                if DEBUG_PRINT:
                    print u"{} | gr1={} | gr2={}".format( test_word,  string_util.joinUtf8( ",", gr1 ), 
                                                                      string_util.joinUtf8( ",", gr2 ) )

                gr1_and_test_word = gr1[:]
                gr1_and_test_word.append( test_word )

                gr2_and_test_word = gr2[:]
                gr2_and_test_word.append( test_word )

                sim0 = model.n_similarity(gr1, gr2)
                sim1 = model.n_similarity(gr1_and_test_word, gr2)
                sim2 = model.n_similarity(gr1,               gr2_and_test_word)
                if DEBUG_PRINT:
                    print "sim0 = {:5.3f}".format( sim0 )
                    print "sim1 = {:5.3f}".format( sim1 )
                    print "sim2 = {:5.3f}".format( sim2 )

                if sim0 > sim1 or sim0 > sim2:
                    sim12_greater_sim0_always = False
                
                if DEBUG_PRINT:
                    a = 1 if sim1 > sim0 else -1
                    b = 1 if sim2 > sim0 else -1
                    #test_word_counter_int += (a + b)/2
                    #test_word_counter_float += (sim1 - sim0) + (sim2 - sim0)
                    #print "test_word_counter_int = {}".format( test_word_counter_int )
                    #print "test_word_counter_float = {}".format( test_word_counter_float )

            if DEBUG_PRINT:
                print ("---")
        #syn_rank      [test_word] = test_word_counter_int;
        #syn_centrality[test_word] = test_word_counter_float;
        #syn_internal  [test_word] = sim12_greater_sim0_always;
        
        if sim12_greater_sim0_always:
            result_int_s.append( test_word )

        if DEBUG_PRINT:
            print ("+++++++")
            print
        i += 1

    return result_int_s


# This function should be called if the fuction getInternalSet failed (returned empty IntS).
def getInternalSetWithReducing( arr_words, target_word, model):
    """
    Get IntS (internal, kernel) words for words in the array arr_words. 
        If |IntS (arr_words)| == 0 then try reduce arr_words, 
        (1) until |IntS (reduced arr_words)| > 0
        (2)  and    set (reduced arr_words) contains target_word
    
    
    Parameters
    ----------
    arr_words : array of Strings
        Array of source words, for example a synonym set or a sentence' words.
    target_word : String
        (1) arr_words contain target_word, 
        (2) the result IntS should contain target_word too.
    model : object
        Word2Vec model.
        
    Returns
    -------
    array of Strings
        Internal subset for the source list of words (arr_words) or for subset of arr_words.
        Empty array if there are no such words.
    """
        
    #result_int_s = []

    arr_words = filter_vocab_words.filterVocabWords( arr_words, model.vocab )
    #print string_util.joinUtf8( ",", arr_words )                            # after filter, now there are only words with vectors
    
    #if len(arr_words) < 3:
    #    return []       
    
    while len(arr_words) >= 3:  # it is possible calculate IntS only when there are >= 3 words
    
        int_s = getInternalSet (arr_words, model)
        if len( int_s ) > 0:
            return int_s

        # then now: len (int_s) == 0
        # let's find word_remote (1) within arr_words, (2) the most distant word to the target word
        target_vector = model [ target_word ]
        word_remote = ""
        #arr_new = []
        sim_min = 1.0
        for word in arr_words:
            if word == target_word:
                continue            # let's skip and do not delete target word itself

            vector = model [ word ]
            sim = 1 - spatial.distance.cosine( target_vector, vector )

            #print u"sim({}, {}) = {}".format( target_word, word, sim )

            if sim < sim_min:
                #print u"UPDATE: new sim {} < sim_min {}, word_remote old = {}, new = {}".format( sim, sim_min, word_remote, word )
                sim_min     = sim
                word_remote = word
            #print

        if len( word_remote ) == 0: # it is very strange that we did not find any word!
            return []

        arr_words.remove( word_remote )
        print string_util.joinUtf8( ",", arr_words )
    
    return []
    #return result_int_s
