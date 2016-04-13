#!/usr/bin/env python
# -*- coding: utf-8 -*-

# for each word in neural net model (RusVectores)

# 1. Find epsilon-neighborhood of word w (vector v)
#       -> eps(w) = word_1, ... word_n1 (gets model.most_similar == top_n1 similar words, distance from w <= Epsilon)
# 2. Word w -> vector v -> vector -v -> word -w.
# 3. Find epsilon-neighborhood of word -w (vector -v)
#       -> eps(-w) = -word_1, ... -word_n2 (gets model.most_similar == top_n similar words, distance from -w <= Epsilon)
# 4. sim( eps(w), eps(-w) ) = 
#       = model.n_similarity ( word_1, ... word_n1,  -word_1, ... -word_n2) = result

import logging
import sys
import os
import operator

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import Word2Vec
import numpy as np

from scipy import spatial # Cosine similarity calculation

sys.path.append(os.path.abspath('../')) # add parent folder, access to 'lib'
import lib.filter_vocab_words
import lib.string_util
import lib.synset
import lib.average_vector
import lib.epsilon_neighborhood

import configus
model = Word2Vec.load_word2vec_format(configus.MODEL_PATH, binary=True)


# ruscorpora
# 0.3 too noisy... try 0.45
eps_plus  = 0.47    # 0.45
eps_minus = 0.34    # 0.34

#news
#eps_plus = 0.35
#eps_minus = 0.12

i = 0

word_epsilons = dict() # dictionary of WordSim's objects

for word in model.vocab:
    i += 1
    dist = lib.epsilon_neighborhood.getDistanceAverageEpsilonNeighborhoodAndNegative( word, eps_plus, eps_minus, model, np )
    
    # do not store words with 0.0 distance (it is special return value - failed)
    #print u" word={}, dist (v, -v)={}, abs dist={}".format( word, dist, abs(dist) )
    
    if abs(dist) > 0.000001:
        print
        print u" word={}, dist (v, -v)={}".format( word, dist )
        word_epsilons[ word ] = dist
    
    #sys.exit()
    
    if i > 90:
        break


sorted_words_by_eps = sorted(word_epsilons.items(), key=operator.itemgetter(1))

print 
print "Similarity from positive to negative set sim( eps(w), eps(-w) ) -----------"
for _word_sim in sorted_words_by_eps:
    
    print u" word={}, dist (v, -v)={}".format( _word_sim[0], _word_sim[1] )
    #print u" word={}".format( _word_sim )
    #print u" word={}, dist (v, -v)={}".format( _word_sim, sorted_words_by_eps [_word_sim] )
    


