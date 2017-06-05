#!/usr/bin/env python
# -*- coding: utf-8 -*-

import logging
import sys
import os

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import word2vec, keyedvectors
import numpy as np
import re
import itertools

sys.path.append(os.path.abspath('../')) # add parent folder, access to 'lib'
import lib.filter_vocab_words
import lib.string_util
#import lib.synset
import lib.average_vector

import configus
model = keyedvectors.KeyedVectors.load_word2vec_format(configus.MODEL_PATH, binary=True)


# example with 2 pair of two near words (near in RusVectores)
# similarity (медведь, зверь) = 
# similarity (поплыть, плыть) = 
#

#source_text = u'ОН ПЛОТНЫЙ ЗАСТЕГНУТЬ СВОЙ ЛЁГКИЙ ПИДЖАЧОК ВЕТЕР ПРОНИЗЫВАТЬ ЕГО НАСКВОЗЬ'
source_text = u'плотный плыть медведь'
#source_text = u'УЖЕ НЕСКОЛЬКО ЛЕТ ГОСТЕПРИИМНЫЙ ТЮРЬМА НА ОСТРОВ СЛУЖИТЬ ОН ЗИМНИЙ КВАРТИРА'
#source_text = u'СЛУЖИТЬ КУХНЯ КАК СНАЧАЛА ГОРНИЧНАЯ УБИРАТЬ КОМНАТА НЕМНОГОЧИСЛЕННЫЙ ЖИЛЕЦ ЧИСТИТЬ ОН ОБУВЬ ПЛАТЬЕ ПОДАВАТЬ САМОВАР БЕГАТЬ БУЛОЧНАЯ'
print "Text = " + source_text

#synonym_text = u'запахнуть одежда дуновение проникать сквозь' # 0.887802541256 плотный
#synonym_text = u'одеться одежда дуновение проникать сквозь' # 0.870945632458 плотный
synonym_text = u'слон прыгать' # 0.870945632458 плотный
#synonym_text = u'поплыть зверь' # 0.870945632458 плотный

# split text to words[]
delim = ' \n\t,.!?:;';  # see http://stackoverflow.com/a/790600/1173350
sentence_words = re.split("[" + delim + "]", source_text.lower())
print "Words in sentence: " + u', '.join(sentence_words)

#words = [u'застегнуть', u'пиджачок']
#for w in words:
#    print u'word {0} '.format(w)
#    v_word = model[ w ]
#    print u'word {0} {1}'.format(w, v_word)

# let's filter out words without vectors, that is remain only words, which are presented in RusVectores dictionary
words = lib.filter_vocab_words.filterVocabWords( sentence_words, model.vocab )
print "Words in RusVectores: " + u', '.join(words)

sentences = []
for target_word in words:

    print "target word: " + target_word

    # sentence without target word
    sentence_minus_target = words[:]
    sentence_minus_target.remove( target_word )
    print "Words in sentence without target word: " + u', '.join(sentence_minus_target)

    # calculate sentence average vector
    average_sentence = lib.average_vector.getAverageVectorForWords( sentence_minus_target, model, np )
    # print "sentence average_vector: {}".format( average_sentence )

    # replace one word in sentence by synonym (replace w_remove by w_add)
    from synonyms_data import word_syn


#temp gvim

    syn_words = re.split("[" + delim + "]", synonym_text.lower())
    print "Words in sentence with synonyms: " + u', '.join(syn_words)

    sentence_aver_vect_with_syn_wotarget = lib.average_vector.getAverageVectorForWords( syn_words, model, np )

    ###vect_target_syn = model[ target_word ] - average_sentence + sentence_aver_vect_with_syn_wotarget

    # step 1.
    vect_target_syn = model[ target_word ] 
    # print "Step 1. vect_target_syn = model[ target_word ] {}".format( vect_target_syn )

    # step 2.
    vect_target_syn = model[ target_word ] - average_sentence
    # print "Step 2. vect_target_syn = model[ target_word ] - average_sentence {}".format( vect_target_syn )

    # step 3.
    vect_target_syn = vect_target_syn + sentence_aver_vect_with_syn_wotarget
    # print "Step . vect_target_syn += sentence_aver_vect_with_syn_wotarget {}".format( vect_target_syn )

    # vect_target_syn = np.subtract( model[ target_word ], np.add( average_sentence, sentence_aver_vect_with_syn_wotarget ))
    #print "vect_target_syn: {}".format( vect_target_syn )

    # print "sentence_aver_vect_with_syn_wotarget - average_sentence: {}".format( sentence_aver_vect_with_syn_wotarget - average_sentence )

    arr_target_synonyms = model.similar_by_vector(vect_target_syn, topn=10, restrict_vocab=None)
    #        print "Calculated target synonyms: " + u', '.join(arr_target_synonyms)
    for i in range(0, 9):
        print u'{0} {1}'.format(arr_target_synonyms[i][0], arr_target_synonyms[i][1])


    cosine_similarity = np.dot(average_sentence, sentence_aver_vect_with_syn_wotarget)/(np.linalg.norm(average_sentence)* np.linalg.norm(sentence_aver_vect_with_syn_wotarget))
    print u'cosine_similarity = {0}'.format(cosine_similarity)

    sys.exit("\nLet's stop and think.")









