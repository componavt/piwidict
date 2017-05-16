#!/usr/bin/env python
# -*- coding: utf-8 -*-

import logging
import sys
import os

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import word2vec, keyedvectors
import numpy as np
import re

sys.path.append(os.path.abspath('../')) # add parent folder, access to 'lib'
import lib.filter_vocab_words
import lib.string_util
#import lib.synset
#import lib.average_vector

import configus
model = keyedvectors.KeyedVectors.load_word2vec_format(configus.MODEL_PATH, binary=True)

#source_text = u'ОН ПЛОТНЫЙ ЗАСТЕГНУТЬ СВОЙ ЛЁГКИЙ ПИДЖАЧОК ВЕТЕР ПРОНИЗЫВАТЬ ЕГО НАСКВОЗЬ'
#source_text = u'УЖЕ НЕСКОЛЬКО ЛЕТ ГОСТЕПРИИМНЫЙ ТЮРЬМА НА ОСТРОВ СЛУЖИТЬ ОН ЗИМНИЙ КВАРТИРА'
source_text = u'СЛУЖИТЬ КУХНЯ КАК СНАЧАЛА ГОРНИЧНАЯ УБИРАТЬ КОМНАТА НЕМНОГОЧИСЛЕННЫЙ ЖИЛЕЦ ЧИСТИТЬ ОН ОБУВЬ ПЛАТЬЕ ПОДАВАТЬ САМОВАР БЕГАТЬ БУЛОЧНАЯ'
print source_text

# split text to words[]
delim = ' \n\t,.!?:;';  # see http://stackoverflow.com/a/790600/1173350
words = re.split("[" + delim + "]", source_text.lower())
print "Source words: "
print (u', '.join(words))

#words = [u'сосредоточиваться', u'asdf']
#print (word)
#v_word = model[ word ]

words_in_dict = lib.filter_vocab_words.filterVocabWords( words, model.vocab )
print "\nWords in RusVectores dict: "
print (', '.join(words_in_dict))
