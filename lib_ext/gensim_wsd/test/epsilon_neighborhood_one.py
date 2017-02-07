#!/usr/bin/env python
# -*- coding: utf-8 -*-

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

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import Word2Vec
import numpy as np

from scipy import spatial # Cosine similarity calculation

sys.path.append(os.path.abspath('../')) # add parent folder, access to 'lib'
import lib.filter_vocab_words
import lib.string_util
import lib.synset
import lib.average_vector

import configus
model = Word2Vec.load_word2vec_format(configus.MODEL_PATH, binary=True)

# run:
# 2/6 = |IntS|/|S|, [[сосредоточиваться]],  IntS(сосредоточиваться сосредотачиваться)  OutS(собираться отвлекаться фокусироваться концентрироваться) 
# source_words = [u'сосредоточиваться', u'сосредотачиваться', u'собираться', u'отвлекаться', u'фокусироваться', u'концентрироваться']
#source_words = [u'лить', u'кутить', u'сосредоточиваться', u'сосредотачиваться', u'собираться', u'отвлекаться', u'фокусироваться', u'концентрироваться']

# 0/6 = |IntS|/|S|, [[абсолют]],  OutS(абсолют логос первооснова творец совершенство идеал) 

#while word in model.vocab:
    #print string_util.joinUtf8( ",", words )
#    out_word = model.doesnt_match(words)
#    print u"    - '{}'".format( out_word )
#    words.remove( out_word )


#word = [u'сосредоточиваться', u'собираться']
#word = [u'собираться']
word = [u'сосредоточиваться']

# How to twice vector (multiply to scalar 2.0)
# v_word = model[ word ]
# mult2 = np.ones(300) + 1
# v_word2 = v_word * mult2
# most_similar_words = model.most_similar( v_word, [], topn)

# 1. Find epsilon-neighborhood of word w (vector v)
#       -> eps(w) = word_1, ... word_n1 (gets model.most_similar == top_n1 similar words, distance from w <= Epsilon)

topn = 20;
#most_similar_words = model.most_similar( ['woman'], [], topn)
most_similar_words_source = model.most_similar( word, [ ], topn)

most_similar_words = lib.filter_vocab_words.filterVocabWordSimilarity( most_similar_words_source, model.wv.vocab )
#print string_util.joinUtf8( ",", words )                                # after filter, now there are only words with vectors

print 
print u"Nearest words to the word: '{}'".format( word[0] )

for sim_w in most_similar_words:
    print u"{}  '{}'".format( sim_w[1], sim_w[0] )


# 2. Word w -> vector v -> vector -v -> word -w.
# 3. Find epsilon-neighborhood of word -w (vector -v)
#       -> eps(-w) = -word_1, ... -word_n2 (gets model.most_similar == top_n similar words, distance from -w <= Epsilon)
print
#print u"word[0] = '{}'".format( word[0] )

vector = model [ word[0] ]
negative_v = np.negative( vector )

#print "vector = model[ word ] = {}".format( vector )
#print
#print "vector = model[ word ] = {}".format( negative_v )

topn = 20;
negative_similar_words = model.most_similar( [ negative_v ], [], topn)

for sim_w in negative_similar_words:
    print u"{}  '{}'".format( sim_w[1], sim_w[0] )
    

# 4. sim( eps(w), eps(-w) ) = 
#       = model.n_similarity ( word_1, ... word_n1,  -word_1, ... -word_n2) = result
    
average_eps_positive = lib.average_vector.getAverageVectorForModelWords( most_similar_words,     model, np )
average_eps_negative = lib.average_vector.getAverageVectorForModelWords( negative_similar_words, model, np )

result = 1 - spatial.distance.cosine( average_eps_positive, average_eps_negative )

print
print "Similarity from positive to negative set sim( eps(w), eps(-w) ) = {}".format( result )
