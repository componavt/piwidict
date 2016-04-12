#!/usr/bin/env python

import math
import average_vector

from scipy import spatial # Cosine similarity calculation

def getDistanceAverageEpsilonNeighborhoodAndNegative( source_word, eps_plus, eps_minus, model, np ):
    """
    Get distance (angle by cosine similarity)
        between 1. epsilon-neighborhood of word w (vector v)
        and     2. epsilon-neighborhood of vector -v (negative mirror of word w)
    
    Parameters
    ----------
    source_word : String
        Source word to be the center of epsilon-neighborhood of similar words.
    eps_plus: float
        filter to include into the positive neighborhood set only words, where dist(source_word, word) < Eps+, 0.3 too noisy... try 0.45
    eps_minus: float
        filter to include into the negative neighborhood set only words, where dist(source_word, word) < Eps-, try 0.3
    model : object
        Word2Vec model.
    np : object
        numpy library.
        
    Returns
    -------
    float
        Cosine (distance) between average vectors of two sets: positive set near vector v (i.e. word w) and negative set around -v.
        0.0, if one of neighborhood sets is empty
    """
    
    # 1. Find epsilon-neighborhood of word w (vector v)
    #       -> eps(w) = word_1, ... word_n1 (gets model.most_similar == top_n1 similar words, distance from w <= Epsilon)
    # 2. Word w -> vector v -> vector -v -> word -w.
    # 3. Find epsilon-neighborhood of word -w (vector -v)
    #       -> eps(-w) = -word_1, ... -word_n2 (gets model.most_similar == top_n similar words, distance from -w <= Epsilon)
    # 4. sim( eps(w), eps(-w) ) = 
    #       = model.n_similarity ( word_1, ... word_n1,  -word_1, ... -word_n2) = result


    # 1. Find epsilon-neighborhood of word w (vector v)
    #       -> eps(w) = word_1, ... word_n1 (gets model.most_similar == top_n1 similar words, distance from w <= Epsilon)

    topn = 10;
    most_similar_words_source = model.most_similar( source_word, [ ], topn)

    #most_similar_words = lib.filter_vocab_words.filterVocabWordSimilarity( most_similar_words_source, model.vocab )
    #print string_util.joinUtf8( ",", words )                                # after filter, now there are only words with vectors

    # debug: print similarity (to source_word) and word itself
    most_similar_words = []
    for sim_w in most_similar_words_source:
        word = sim_w[0]
        sim  = sim_w[1]
        if abs(sim) > eps_plus:
            most_similar_words.append( sim_w )
            
    # sim( eps(w), eps(-w) ) == 0, if one of neighborhood sets is empty
    if 0 == len( most_similar_words ):
        return 0.0


    # 2. Word w -> vector v -> vector -v -> word -w.
    # 3. Find epsilon-neighborhood of word -w (vector -v)
    #       -> eps(-w) = -word_1, ... -word_n2 (gets model.most_similar == top_n similar words, distance from -w <= Epsilon)
    
    negative_similar_words = []
    for positive_word in most_similar_words:
    
        vector = model [ positive_word[0] ]
        negative_v = np.negative( vector )
    
        # debug: print huge nn-model vector
        #print "vector = model[ word ] = {}".format( vector )
        #print
        #print "vector = model[ word ] = {}".format( negative_v )

        negative_similar_words_source = model.most_similar( [ negative_v ], [], topn)
        for sim_w in negative_similar_words_source:
            word = sim_w[0]
            sim  = sim_w[1]
            if abs(sim) > eps_minus:
                negative_similar_words.append( sim_w )
                
                
        
    # sim( eps(w), eps(-w) ) == 0, if one of neighborhood sets is empty
    if 0 == len( negative_similar_words ):
        return 0.0


    # Print section
    print 
    print u"Nearest words to the word: '{}'".format( source_word )
    for sim_w in most_similar_words:
        word = sim_w[0]
        sim  = sim_w[1]
        print u"{}  '{}'".format( sim, word )
        
    print
    print u"--- Nearest words to the negative vector (for each word in positive set):"
    for sim_w in negative_similar_words:
        word = sim_w[0]
        sim  = sim_w[1]
        print u"{}  '{}'".format( sim, word )
        

    # 4. sim( eps(w), eps(-w) ) = 
    #       = model.n_similarity ( word_1, ... word_n1,  -word_1, ... -word_n2) = result

    average_eps_positive = average_vector.getAverageVectorForModelWords( most_similar_words,     model, np )
    average_eps_negative = average_vector.getAverageVectorForModelWords( negative_similar_words, model, np )
        
    result = 1 - spatial.distance.cosine( average_eps_positive, average_eps_negative )

    #print
    #print "Similarity from positive to negative set sim( eps(w), eps(-w) ) = {}".format( result )
    return result
