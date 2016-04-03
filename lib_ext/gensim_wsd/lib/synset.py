#!/usr/bin/python

# Set of synonym as a synset.
class Synset:
    """A synonym set class"""
    
    # name of the dictionary article
    headword = ''
    
    # all words in synset as one string delimitered by space
    line = ''
    
    # size of synset kernel |IntS|, i.e. number of synonyms, which always make subsets more nearer
    ints_len = 0;
    
    # size of synset, i.e. number of words
    len = 0;
    
    # two sublists of this synset: internal words (IntS) and the edge of the synset (OutS)
    ints_words = []
    outs_words = []
    
    #def f(self):
    #    return 'hello world'
