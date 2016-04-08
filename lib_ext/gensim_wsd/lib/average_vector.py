#!/usr/bin/env python

# Calculates average vector for the set of model-word.
# In: word_1, ... word_N
# Out: average_vector ## raw numpy vector of a word ### model['computer'] =
#
def getAverageVectorForModelWords( word_sim_list, model, np ):
    """Calculates average vector for the set of model-word.
    
    Parameters
    ----------
    word_sim_list : array
        Array of model-words in format [(word1, similarity1), (word1, similarity1), ...].
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