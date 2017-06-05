#!/usr/bin/env python

# Calculates average vector for the set of model-word,
# where model word is ('string', similarity)
# In: word_1, ... word_N
# Out: average_vector ## raw numpy vector of a word ### model['computer'] =
#
def getAverageVectorForModelWords( word_sim_list, model, np ):
    """Calculates average vector for the set of model-word.
    
    Parameters
    ----------
    word_sim_list : array
        Array of model-words in format [(word1, similarity1), (word2, similarity2), ...].
    model : object
        Word2Vec model.
    np : object
        numpy library.
        
    Returns
    -------
    array
        multidimensional vector, float.
    """

    arr_vectors = []
    for word_sim in word_sim_list:
        #print u"  - '{}'".format( model[ word_sim[0] ] )
        arr_vectors.append(       model[ word_sim[0] ] )
    
    average_vector = np.average( arr_vectors, axis=0)
    #print " average_vector: {}".format( average_vector )

    return average_vector



def getAverageVectorForWords( word_list, model, np ):
    """Calculates average vector for the set of word.
    
    Parameters
    ----------
    word_list : array
        Array of words in format [word1, word2, ...].
    model : object
        Word2Vec model.
    np : object
        numpy library.
        
    Returns
    -------
    array
        multidimensional vector, float.
    """

    arr_vectors = []
    for w in word_list:
#        print u'word {0} {1}'.format(w, model[ w ])
        arr_vectors.append( model[ w ] )
    average_vector = np.average( arr_vectors, axis=0)
#    print u'average_vector {0} '.format(average_vector)

    return average_vector
