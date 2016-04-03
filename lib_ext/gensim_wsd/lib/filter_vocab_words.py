#!/usr/bin/python

# Filters words, skip words which are absent in the vocabulary 'vocab'.
def filterVocabWords( word_list, vocab ):
    "Skip words which are absent in the model vocabulary"    
    
    # result filtered list
    result = []
    
    for w in word_list:
        if w in vocab:
            result.append(w)
            
        #if w.decode('utf8') not in vocab:
        #if w not in vocab:
        #    word_list.remove( w )
        #    print u"My KeyError: '{}' does not indexed by word2vec model.".format( w )
        #else:
        #    print u"OK. '{}' indexed by word2vec model.".format( w )
    
    return result
