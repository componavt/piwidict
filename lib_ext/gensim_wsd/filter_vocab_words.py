#!/usr/bin/python

# Filters words, skip words which are absent in the vocabulary 'vocab'.
def filterVocabWords( word_list, vocab ):
    "Skip words which are absent in the model vocabulary"
       
    for w in word_list:
        #if w.decode('utf8') not in vocab:
        if w not in vocab:
            word_list.remove( w )
            
            #print "My KeyError: The word '{}' does not indexed by word2vec model.".format( w )
        #else:
            # print "The word '{}' indexed by word2vec model.".format( w )   
    return;
