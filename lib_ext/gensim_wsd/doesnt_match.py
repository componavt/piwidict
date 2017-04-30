#!/usr/bin/env python
# -*- coding: utf-8 -*-

# Prints list of synonyms in synset. -1 the most outer word, ... while |synset|>0

import logging
import codecs
import operator
import collections

import sys
reload(sys)
sys.setdefaultencoding("utf-8")

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import Word2Vec

import lib.filter_vocab_words
import lib.string_util
import lib.synset

import configus
model = Word2Vec.load_word2vec_format(configus.MODEL_PATH, binary=True)

# 2/6 = |IntS|/|S|, [[сосредоточиваться]],  IntS(сосредоточиваться сосредотачиваться)  OutS(собираться отвлекаться фокусироваться концентрироваться) 
# source_words = [u'сосредоточиваться', u'сосредотачиваться', u'собираться', u'отвлекаться', u'фокусироваться', u'концентрироваться']
source_words = [u'лить', u'кутить', u'сосредоточиваться', u'сосредотачиваться', u'собираться', u'отвлекаться', u'фокусироваться', u'концентрироваться']

# 0/6 = |IntS|/|S|, [[абсолют]],  OutS(абсолют логос первооснова творец совершенство идеал) 

words = lib.filter_vocab_words.filterVocabWords( source_words, model.wv.vocab )
#print string_util.joinUtf8( ",", words )                                # after filter, now there are only words with vectors

while words:
    #print string_util.joinUtf8( ",", words )
    out_word = model.doesnt_match(words)
    print u"    - '{}'".format( out_word )
    words.remove( out_word )
