#!/usr/bin/env python
# -*- coding: utf-8 -*-

import logging
import sys
import os
import codecs
import operator
import collections

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import Word2Vec

sys.path.append(os.path.abspath('../')) # add parent folder, access to 'lib'
import lib.filter_vocab_words
import lib.string_util
import lib.synset

import lib.internal_set

import configus
model = Word2Vec.load_word2vec_format(configus.MODEL_PATH, binary=True)

# *) every line is a set of synonym words (synset)

print "Word2vec model: {}\n".format(configus.MODEL_NAME)


# Exampl 1. Empty IntS
# #####################
#0/7 = |IntS|/|S|, [[план]],  OutS(умысел намерение прожект задумка план проект замысел) 

line = u"план умысел намерение прожект задумка проект замысел"

arr_words = line.split()
int_s = lib.internal_set.getInternalSet (arr_words, model)

print line
print u"IntS = ({})".format( lib.string_util.joinUtf8( ",", int_s ) )
print


# Exampl 2. Non-empty IntS
# #####################
# 1/5 = |IntS|/|S|, [[шум]],  IntS(шум)  OutS(гам гвалт грохот гул) 
line = u"шум гам гвалт грохот гул"

# 2/4 = |IntS|/|S|, [[сражение]],  IntS(битва сражение)  OutS(баталия бой) 
line = u"битва сражение баталия бой"

arr_words = line.split()
int_s = lib.internal_set.getInternalSet (arr_words, model)

print line
print u"IntS = ({})".format( lib.string_util.joinUtf8( ",", int_s ) )
