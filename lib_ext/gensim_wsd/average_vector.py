#!/usr/bin/env python
# -*- coding: utf-8 -*-

# Calculates average vector for the set of word.
# In: word_1, ... word_N
# Out: average_vector ## raw numpy vector of a word ### model['computer'] =

import logging
import filter_vocab_words
import sys
import string_util
import codecs
import operator
import collections
import synset

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import Word2Vec
import numpy as np


model = Word2Vec.load_word2vec_format("/data/all/soft_new/linguistics/rusvectores/ruscorpora.model.bin", binary=True) # hasee
#model = Word2Vec.load_word2vec_format("/media/data/all/soft_new/linguistics/rusvectores/" + model_name + ".model.bin", binary=True) # home


# 2/6 = |IntS|/|S|, [[сосредоточиваться]],  IntS(сосредоточиваться сосредотачиваться)  OutS(собираться отвлекаться фокусироваться концентрироваться) 
source_words = [u'сосредоточиваться', u'сосредотачиваться', u'собираться', u'отвлекаться', u'фокусироваться', u'концентрироваться']

#word1 = [1,2,3,0]
#word2 = [1,2,3,0]
#word3 = [1,2,3,4]

#arr_vectors = []
#arr_vectors.append( word1 )
#arr_vectors.append( word2 )
#arr_vectors.append( word3 )

#print arr_vectors
#print np.average( arr_vectors, axis=0)


words = filter_vocab_words.filterVocabWords( source_words, model.vocab )
print string_util.joinUtf8( ",", words )                                # after filter, now there are only words with vectors

arr_vectors = []
for w in words:
    # print u"    - '{}'".format( model[ w ] )
    arr_vectors.append( model[ w ] )
    
average_vector = np.average( arr_vectors, axis=0)
#print " average_vector: {}".format( average_vector )

# How to find the closest word to a vector using word2vec
# http://stackoverflow.com/questions/32759712/how-to-find-the-closest-word-to-a-vector-using-word2vec

model_word_vector = np.array( average_vector, dtype='f') # dtype=float32

topn = 20;
most_similar_words = model.most_similar( [ model_word_vector ], [], topn)

print 
print "Distance and words itself of {} words which are nearest to the average vector:".format( topn )

for sim_w in most_similar_words:
    print u"{}  '{}'".format( sim_w[1], sim_w[0] )
