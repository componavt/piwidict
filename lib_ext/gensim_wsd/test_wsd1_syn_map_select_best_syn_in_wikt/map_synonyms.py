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
import lib.average_vector

import configus
model = keyedvectors.KeyedVectors.load_word2vec_format(configus.MODEL_PATH, binary=True)

#source_text = u'ОН ПЛОТНЫЙ ЗАСТЕГНУТЬ СВОЙ ЛЁГКИЙ ПИДЖАЧОК ВЕТЕР ПРОНИЗЫВАТЬ ЕГО НАСКВОЗЬ'
#source_text = u'УЖЕ НЕСКОЛЬКО ЛЕТ ГОСТЕПРИИМНЫЙ ТЮРЬМА НА ОСТРОВ СЛУЖИТЬ ОН ЗИМНИЙ КВАРТИРА'
source_text = u'СЛУЖИТЬ КУХНЯ КАК СНАЧАЛА ГОРНИЧНАЯ УБИРАТЬ КОМНАТА НЕМНОГОЧИСЛЕННЫЙ ЖИЛЕЦ ЧИСТИТЬ ОН ОБУВЬ ПЛАТЬЕ ПОДАВАТЬ САМОВАР БЕГАТЬ БУЛОЧНАЯ'
print "Text = " + source_text

# split text to words[]
delim = ' \n\t,.!?:;';  # see http://stackoverflow.com/a/790600/1173350
words = re.split("[" + delim + "]", source_text.lower())
print "Source words: " + u', '.join(words)

#words = [u'сосредоточиваться', u'asdf']
#print (word)
#v_word = model[ word ]

# let's filter out words without vectors, that is remain only words, which are presented in RusVectores dictionary
words_in_dict = lib.filter_vocab_words.filterVocabWords( words, model.vocab )
#print string_util.joinUtf8( ",", words_in_dict )                                

# calculate sentence average vector
arr_vectors = []
for w in words_in_dict:
    # print u"    - '{}'".format( model[ w ] )
    arr_vectors.append( model[ w ] )
sentence_average_vector = np.average( arr_vectors, axis=0)
#print "sentence average_vector: {}".format( sentence_average_vector )


# replace one word in sentence by synonym (replace w_remove by w_add)
w_remove = u'плотный'
w_add_array = [ u'густой',
                u'светонепроницаемый', 
                u'частый', u'сплошной',
                u'толстый', u'массивный',
                u'крепкий', u'сбитый', u'упитанный', u'полный',
                u'обильный', u'сытный']

word_syn = {w_remove : w_add_array}

word_syn [u'пронизывать'] = [ 
                u'нанизывать', u'низать',
                u'продевать',
                u'продырявливать', u'протыкать', u'пронзать',
                u'проникать', u'пропитывать']

word_syn [u'несколько'] = [ 
                u'немного',
                u'слегка']

word_syn [u'служить'] = [ 
                u'работать',
                u'подчиняться', u'работать', u'помогать', u'угождать', u'содействовать',
                u'прислуживать', u'повиноваться',
                u'использоваться', u'являться', u'предназначаться'
                ]

for w in word_syn:
    w_remove = w                # replace word
    w_add_array = word_syn[w]   # by word's synonyms

    # if the word is absent in the sentence, then we cannot replace this word, skip this iteration
    if w_remove not in words_in_dict:
        continue

    for w_add in w_add_array:
        if w_add not in model.vocab:
            print "Error: " + w_add + " not in RusVectores dictionary!"

        words_with_syn = [w_add if w == w_remove else w for w in words_in_dict]

        print "\nWords in RusVectores dict   : " + ', '.join(words_in_dict)
        print "One synonym changed in list : " + ', '.join(words_with_syn)
        sim = model.n_similarity(words_in_dict, words_with_syn)
        print "sim = {:5.3f}".format(sim)
