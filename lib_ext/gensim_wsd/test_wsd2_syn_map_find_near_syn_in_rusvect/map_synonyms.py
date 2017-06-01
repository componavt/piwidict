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

source_text = u'ОН ПЛОТНЫЙ ЗАСТЕГНУТЬ СВОЙ ЛЁГКИЙ ПИДЖАЧОК ВЕТЕР ПРОНИЗЫВАТЬ ЕГО НАСКВОЗЬ'
#source_text = u'УЖЕ НЕСКОЛЬКО ЛЕТ ГОСТЕПРИИМНЫЙ ТЮРЬМА НА ОСТРОВ СЛУЖИТЬ ОН ЗИМНИЙ КВАРТИРА'
#source_text = u'СЛУЖИТЬ КУХНЯ КАК СНАЧАЛА ГОРНИЧНАЯ УБИРАТЬ КОМНАТА НЕМНОГОЧИСЛЕННЫЙ ЖИЛЕЦ ЧИСТИТЬ ОН ОБУВЬ ПЛАТЬЕ ПОДАВАТЬ САМОВАР БЕГАТЬ БУЛОЧНАЯ'
print "Text = " + source_text

# split text to words[]
delim = ' \n\t,.!?:;';  # see http://stackoverflow.com/a/790600/1173350
sentence_words = re.split("[" + delim + "]", source_text.lower())
print "Words in sentence: " + u', '.join(sentence_words)

#words = [u'сосредоточиваться', u'asdf']
#print (word)
#v_word = model[ word ]

# let's filter out words without vectors, that is remain only words, which are presented in RusVectores dictionary
words = lib.filter_vocab_words.filterVocabWords( sentence_words, model.vocab )
print "Words in RusVectores: " + u', '.join(words)


for target_word in words:

    # target_word = words[4]
    print "target word: " + target_word

    # sentence without target word
    sentence_minus_target = words[:]
    sentence_minus_target.remove( target_word )
    print "Words in sentence without target word: " + u', '.join(sentence_minus_target)




    # calculate sentence average vector
    arr_vectors = []
    for w in sentence_minus_target:
        # print u"    - '{}'".format( model[ w ] )
        arr_vectors.append( model[ w ] )
    sentence_average_vector = np.average( arr_vectors, axis=0)
    #print "sentence average_vector: {}".format( sentence_average_vector )


    # copy words from sentence_minus_target to words_with_syn
    words_with_syn = sentence_minus_target[:]


    # replace one word in sentence by synonym (replace w_remove by w_add)
    from synonyms_data import word_syn

    w_remove = u'ветер'
    w_add_array = word_syn [w_remove] # load synonyms to the word w_remove from the file synonyms_data.py

    for w in word_syn:
        w_remove = w                # replace word
        w_add_array = word_syn[w]   # by word's synonyms

        # if the word is absent in the sentence, then we cannot replace this word, skip this iteration
        if w_remove not in words:
            continue

        for w_add in w_add_array:
            if w_add not in model.vocab:
                print "Error: " + w_add + " not in RusVectores dictionary!"
                continue

            words_with_syn_new = [w_add if w == w_remove else w for w in words_with_syn]
            words_with_syn = words_with_syn_new[:] # copy words from words_with_syn_new to words_with_syn

            # print "\nWords in RusVectores dict   : " + ', '.join(words)
            print "One synonym changed in list : " + ', '.join(words_with_syn)

            # calculate average vector of sentence with synonym without target word
            arr_vectors = []
            for w in words_with_syn:
                # print u"    - '{}'".format( model[ w ] )
                arr_vectors.append( model[ w ] )
            sentence_aver_vect_with_syn_wotarget = np.average( arr_vectors, axis=0)

            vect_target_syn = model[ target_word ] - sentence_average_vector + sentence_aver_vect_with_syn_wotarget
            # print "vect_target_syn: {}".format( vect_target_syn )

            arr_target_synonyms = model.similar_by_vector(vect_target_syn, topn=1, restrict_vocab=None)
    #        print "Calculated target synonyms: " + u', '.join(arr_target_synonyms)
            print u'{0} {1}'.format(arr_target_synonyms[0][0], arr_target_synonyms[0][1])

            sys.exit("\nLet's stop and think.")





