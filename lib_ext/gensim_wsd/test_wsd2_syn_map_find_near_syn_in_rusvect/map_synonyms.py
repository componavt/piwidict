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

from synonyms_data import word_syn

import configus
model = keyedvectors.KeyedVectors.load_word2vec_format(configus.MODEL_PATH, binary=True)

#source_text = u'ОН ПЛОТНЫЙ ЗАСТЕГНУТЬ СВОЙ ЛЁГКИЙ ПИДЖАЧОК ВЕТЕР ПРОНИЗЫВАТЬ ЕГО НАСКВОЗЬ'
source_text = u'ПЛОТНЫЙ ЗАСТЕГНУТЬ ПИДЖАЧОК ЛЕГКИЙ'
#source_text = u'УЖЕ НЕСКОЛЬКО ЛЕТ ГОСТЕПРИИМНЫЙ ТЮРЬМА НА ОСТРОВ СЛУЖИТЬ ОН ЗИМНИЙ КВАРТИРА'
#source_text = u'СЛУЖИТЬ КУХНЯ КАК СНАЧАЛА ГОРНИЧНАЯ УБИРАТЬ КОМНАТА НЕМНОГОЧИСЛЕННЫЙ ЖИЛЕЦ ЧИСТИТЬ ОН ОБУВЬ ПЛАТЬЕ ПОДАВАТЬ САМОВАР БЕГАТЬ БУЛОЧНАЯ'
print "Text = " + source_text

# split text to words[]
delim = ' \n\t,.!?:;';  # see http://stackoverflow.com/a/790600/1173350
sentence_words = re.split("[" + delim + "]", source_text.lower())
print "Words in sentence: " + u', '.join(sentence_words)

# let's filter out words without vectors, that is remain only words, which are presented in RusVectores dictionary
words = lib.filter_vocab_words.filterVocabWords( sentence_words, model.vocab )
print "Words in RusVectores: " + u', '.join(words)

###sentences = []
for target_word in words:

    print "target word: " + target_word

    # sentence without target word
    sentence_minus_target = words[:]
    sentence_minus_target.remove( target_word )
    print "Words in sentence without target word: " + u', '.join(sentence_minus_target)

    # calculate sentence average vector
    average_sentence = lib.average_vector.getAverageVectorForWords( sentence_minus_target, model, np )
    # print "sentence average_vector: {}".format( average_sentence )

    # replace one word in sentence by synonym from word_syn (replace w_remove by w_add)



    # get list of synonyms of words from sentence, invert subset of map word_syn
    synonyms_to_word = dict()
    for w in sentence_minus_target:
        # print u'Next word w: {}'.format( w )
        if w in word_syn:
            # print u"    word_syn[w] = {}".format( word_syn[w] )
            for syn in word_syn[w] :
                # print u'        syn: {}'.format( syn )
                if syn in model.vocab:
                    synonyms_to_word[ syn ] = w

    # print "Synonyms of words in sentence: " #+ u', '.join(synonyms_to_word)
    for syn, w in synonyms_to_word.items():
        print u"synonym <- word   '{}' <- '{}'".format( syn, w)


    # all combinations of synonyms of words in sentence without the target word, 
    # every combination of synonyms will replace words in sentence
    i = 0
    synonyms = synonyms_to_word.keys
    print synonyms
    for L in range(0, len(synonyms)+1):
      for subset in itertools.combinations(synonyms, L):
        i += 1
        print u'{0} {1}'.format(i, ', '.join(subset))

        sentence = sentence_minus_target[:] # copy words from sentence_minus_target
    
        # replace subset in sentence_minus_target by synonyms from our dictionary (word_syn)
        for syn in subset:
            # w_remove does exists in word_syn
            w_remove = synonyms_to_word[ syn ]
            sentence.remove( w_remove )
            sentence.add( syn )

            # print "\nWords in RusVectores dict   : " + ', '.join(words)
        print "new : " + ', '.join(sentence)  # One synonym changed in list

        #if words_with_syn == sentence_minus_target:
        #    continue # no one word was replaced by synonyms, skip

    sys.exit("\nLet's stop and think.")
    words_with_syn = sentence




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

            vect_target_syn = model[ target_word ] - average_sentence + sentence_aver_vect_with_syn_wotarget
            # print "vect_target_syn: {}".format( vect_target_syn )

            arr_target_synonyms = model.similar_by_vector(vect_target_syn, topn=1, restrict_vocab=None)
    #        print "Calculated target synonyms: " + u', '.join(arr_target_synonyms)
            print u'{0} {1}'.format(arr_target_synonyms[0][0], arr_target_synonyms[0][1])

            sys.exit("\nLet's stop and think.")





