#!/usr/bin/env python
# -*- coding: utf-8 -*-

# Prints list of synonyms in synset. -1 the most outer word, ... while |synset|>0

import logging
import filter_vocab_words
#import sys
import string_util
import codecs
import operator
import collections
import synset

import sys
reload(sys)
sys.setdefaultencoding("utf-8")

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import Word2Vec

# run:
# 
#model_name = "ruscorpora"
model_name = "news"
#model_name = "ruwikiruscorpora"

#model = Word2Vec.load_word2vec_format("/data/all/soft_new/linguistics/rusvectores/ruscorpora.model.bin", binary=True) # hasee
model = Word2Vec.load_word2vec_format("/media/data/all/soft_new/linguistics/rusvectores/" + model_name + ".model.bin", binary=True) # home

# 2/6 = |IntS|/|S|, [[сосредоточиваться]],  IntS(сосредоточиваться сосредотачиваться)  OutS(собираться отвлекаться фокусироваться концентрироваться) 
# source_words = [u'сосредоточиваться', u'сосредотачиваться', u'собираться', u'отвлекаться', u'фокусироваться', u'концентрироваться']
source_words = [u'лить', u'кутить', u'сосредоточиваться', u'сосредотачиваться', u'собираться', u'отвлекаться', u'фокусироваться', u'концентрироваться']

# 0/6 = |IntS|/|S|, [[абсолют]],  OutS(абсолют логос первооснова творец совершенство идеал) 

words = filter_vocab_words.filterVocabWords( source_words, model.vocab )
#print string_util.joinUtf8( ",", words )                                # after filter, now there are only words with vectors

while words:
    #print string_util.joinUtf8( ",", words )
    out_word = model.doesnt_match(words)
    print u"    - '{}'".format( out_word )
    words.remove( out_word )