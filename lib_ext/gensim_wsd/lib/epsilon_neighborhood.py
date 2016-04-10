#!/usr/bin/env python

import average_vector

from scipy import spatial # Cosine similarity calculation

def getDistanceAverageEpsilonNeighborhoodAndNegative( source_word, eps, model, np ):
    """
    Get distance (angle by cosine similarity)
        between 1. epsilon-neighborhood of word w (vector v)
        and     2. epsilon-neighborhood of vector -v (negative mirror of word w)
    
    Parameters
    ----------
    source_word : String
        Source word to be the center of epsilon-neighborhood of similar words.
    eps: float
        filter to include into neighborhood set only word, where dist(source_word, word) < Eps
    model : object
        Word2Vec model.
    np : object
        numpy library.
        
    Returns
    -------
    float
        Cosine (distance) between average vectors of two sets: positive set near vector v (i.e. word w) and negative set around -v.
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

    topn = 20;
    most_similar_words_source = model.most_similar( source_word, [ ], topn)

    #most_similar_words = lib.filter_vocab_words.filterVocabWordSimilarity( most_similar_words_source, model.vocab )
    #print string_util.joinUtf8( ",", words )                                # after filter, now there are only words with vectors

    print 
    print u"Nearest words to the word: '{}'".format( source_word )

    # debug: print similarity (to source_word) and word itself
    most_similar_words = []
    for sim_w in most_similar_words_source:
        word = sim_w[0]
        sim  = sim_w[1]
        if sim > eps:
            most_similar_words.append( sim_w )
            print u"{}  '{}'".format( sim, word )


    # 2. Word w -> vector v -> vector -v -> word -w.
    # 3. Find epsilon-neighborhood of word -w (vector -v)
    #       -> eps(-w) = -word_1, ... -word_n2 (gets model.most_similar == top_n similar words, distance from -w <= Epsilon)
    
    vector = model [ source_word ]
    negative_v = np.negative( vector )
    
    # debug: print huge nn-model vector
    #print "vector = model[ word ] = {}".format( vector )
    #print
    #print "vector = model[ word ] = {}".format( negative_v )

    topn = 20;
    negative_similar_words = model.most_similar( [ negative_v ], [], topn)

    print
    for sim_w in negative_similar_words:
        print u"{}  '{}'".format( sim_w[1], sim_w[0] )


    # 4. sim( eps(w), eps(-w) ) = 
    #       = model.n_similarity ( word_1, ... word_n1,  -word_1, ... -word_n2) = result

    average_eps_positive = average_vector.getAverageVectorForModelWords( most_similar_words,     model, np )
    average_eps_negative = average_vector.getAverageVectorForModelWords( negative_similar_words, model, np )

    print
    print "average_eps_positive = {}".format( average_eps_positive )
    print 
    print "average_eps_negative = {}".format( average_eps_negative )
    #print "join(average_eps_positive) = {}".format( join(average_eps_positive) )
    
    #if len( average_eps_positive ) > 0 and len( average_eps_negative ) > 0:
    if np.isnan( average_eps_positive ) or np.isnan( average_eps_negative ):
        result = 0.0
    else:
        result = 1 - spatial.distance.cosine( average_eps_positive, average_eps_negative )

    print
    print "Similarity from positive to negative set sim( eps(w), eps(-w) ) = {}".format( result )
    return result
