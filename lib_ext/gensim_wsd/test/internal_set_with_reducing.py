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


# Exampl 1. Non-empty IntS
# #####################
# 1/5 = |IntS|/|S|, [[шум]],  IntS(шум)  OutS(гам гвалт грохот гул) 

line = u"шум гам гвалт грохот гул"

arr_words   = line.split()
target_word = u"шум"

print u"target_word = {}, line = ({})".format( target_word, line )
print

int_s = lib.internal_set.getInternalSetWithReducing (arr_words, target_word, model)

print line
print u"IntS = ({})".format( lib.string_util.joinUtf8( ",", int_s ) )
print


# Exampl 2. Empty IntS
# #####################
#0/7 = |IntS|/|S|, [[план]],  OutS(умысел намерение прожект задумка план проект замысел) 

line = u"план умысел намерение прожект задумка проект замысел"

arr_words = line.split()
#target_word= u"прожект"
for target_word in arr_words:

    print u"target_word = {}".format( target_word )
    print u"line = ({})".format( line )
    print

    int_s = lib.internal_set.getInternalSetWithReducing (arr_words, target_word, model)

    print u"IntS = ({})".format( lib.string_util.joinUtf8( ",", int_s ) )
    print
    print "------------------"
    print