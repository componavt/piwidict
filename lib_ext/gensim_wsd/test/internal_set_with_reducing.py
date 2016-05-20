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
print "------------------"
print "------------------"
print "------------------"
print


# Exampl 2. Empty IntS
# #####################
#0/7 = |IntS|/|S|, [[план]],  OutS(умысел намерение прожект задумка план проект замысел) 

# RUSCORPORA
line = u"план умысел намерение прожект задумка проект замысел"

#0/5 = |IntS|/|S|, [[хвороба]],  OutS(нездоровье хворость хвороба хворь болезнь) 
line = u"нездоровье хворость хвороба хворь болезнь"

#0/5 = |IntS|/|S|, [[прекрасно]],  OutS(чудесно замечательно отлично превосходно прекрасно) 
#0/5 = |IntS|/|S|, [[добрый]],  OutS(душевный добросердечный отзывчивый сердечный добрый) 
#0/5 = |IntS|/|S|, [[каменный]],  OutS(каменный бесчувственный суровый жестокий безжалостный) 

#line = u"хлопотня замешательство мельтешня беготня кавардак хаос сумятица суета кутерьма беспорядок суматоха неразбериха"
#line = u"злополучие злоключение горе трагедия катастрофа бедствие беда"
#line = u"составление агрегация кооперация соединение слияние интеграция объединение"

# NEWS
#0/5 = |IntS|/|S|, [[обличать]],  OutS(обличать изобличать обвинять разоблачать уличать) 
line = u"обличать изобличать обвинять разоблачать уличать"

#0/4 = |IntS|/|S|, [[казаться]],  OutS(сдаваться представляться думаться казаться) +++
line = u"сдаваться представляться думаться казаться"

#0/7 = |IntS|/|S|, [[изготовлять]],  OutS(делать создавать производить сооружать мастерить изготавливать изготовлять) 
line = u"делать создавать производить сооружать мастерить изготавливать изготовлять"

line = u"план умысел намерение прожект задумка проект замысел"
#line = u"план умысел намерение прожект задумка проект замысел бриз мачта ветер корабль туман" several senses

arr_words = line.split()
#target_word= u"прожект"
for target_word in arr_words:

    print u"target_word = {}".format( target_word )
    print u"line = ({})".format( line )
    #print

    int_s = lib.internal_set.getInternalSetWithReducing (arr_words, target_word, model)

    print u"IntS = ({})".format( lib.string_util.joinUtf8( ",", int_s ) )
    print
    print "------------------"
    print