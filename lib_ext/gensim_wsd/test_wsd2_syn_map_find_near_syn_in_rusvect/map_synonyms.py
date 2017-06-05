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
#source_text = u'ПЛОТНЫЙ ЗАСТЕГНУТЬ ПИДЖАЧОК ЛЕГКИЙ'
#source_text = u'УЖЕ НЕСКОЛЬКО ЛЕТ ГОСТЕПРИИМНЫЙ ТЮРЬМА НА ОСТРОВ СЛУЖИТЬ ОН ЗИМНИЙ КВАРТИРА'
source_text = u'СЛУЖИТЬ КУХНЯ КАК СНАЧАЛА ГОРНИЧНАЯ УБИРАТЬ КОМНАТА НЕМНОГОЧИСЛЕННЫЙ ЖИЛЕЦ ЧИСТИТЬ ОН ОБУВЬ ПЛАТЬЕ ПОДАВАТЬ САМОВАР БЕГАТЬ БУЛОЧНАЯ'
print "Text = " + source_text

# split text to words[]
delim = ' \n\t,.!?:;';  # see http://stackoverflow.com/a/790600/1173350
sentence_words = re.split("[" + delim + "]", source_text.lower())
print "Words in sentence: " + u', '.join(sentence_words)

# let's filter out words without vectors, that is remain only words, which are presented in RusVectores dictionary
words = lib.filter_vocab_words.filterVocabWords( sentence_words, model.vocab )
print "Words in RusVectores: " + u', '.join(words)


# replace several words in sentence by synonyms from word_syn (replace w_remove by w_add), try all combinations of synonyms
generated_sentences_counter = 0
for target_word in words:
    sentences_list = list()

    # print "target word: " + target_word

    # sentence without target word
    sentence_minus_target = words[:]
    sentence_minus_target.remove( target_word )
    ## print "Words in sentence without target word: " + u', '.join(sentence_minus_target)

    # calculate sentence average vector
    average_sentence = lib.average_vector.getAverageVectorForWords( sentence_minus_target, model, np )
    # print "sentence average_vector: {}".format( average_sentence )

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
    #for syn, w in synonyms_to_word.items():
    #    print u"    synonym <- word   '{}' <- '{}'".format( syn, w)

    # all combinations of synonyms of words in sentence without the target word, 
    # every combination of synonyms will replace words in sentence
    i = 0
    synonyms = list(synonyms_to_word.keys())
    #print "Synonyms of words in sentence: " + u', '.join(synonyms)

    for L in range(0, len(synonyms)+1):
      for subset in itertools.combinations(synonyms, L):
        # print u'{0} {1}'.format(i, ', '.join(subset))

        sentence = sentence_minus_target[:] # copy words from sentence_minus_target
    
        # replace subset in sentence_minus_target by synonyms from our dictionary (word_syn)
        for syn in subset:
            w_remove = synonyms_to_word[ syn ]  # w_remove does exists in word_syn, since all keys of synonyms_to_word are presented in model.vocab 
            # print u'    word remove ({0}), synonym add ({1})'.format(w_remove, syn)
            while w_remove in sentence: 
                sentence.remove( w_remove )
                sentence.append( syn )

        # check that this is new sentence, skip repetitions
        new_sentence = True

        ss = set(sentence)
        for phrase in sentences_list:
            if phrase == ss:
                new_sentence = False
                break
        if new_sentence:
            sentences_list.append( ss ) 
            i += 1
            # print u'{0} {1}'.format(i, ', '.join(sentence)) # with synonyms instead of words
    generated_sentences_counter += len(sentences_list)

    i = 0
    for words_with_syn in sentences_list:

        # calculate sentence with synonyms without target word average vector
        average_sentence_with_syn_wotarget = lib.average_vector.getAverageVectorForWords( words_with_syn, model, np )

        vect_target_syn = model[ target_word ] - average_sentence + average_sentence_with_syn_wotarget
        # print "vect_target_syn: {}".format( vect_target_syn )

        arr_target_synonyms = model.similar_by_vector(vect_target_syn, topn=1, restrict_vocab=None)

        # print results
        # print "Calculated target synonyms: " + u', '.join(arr_target_synonyms)
        i += 1
        target_synonym = arr_target_synonyms[0][0]
        # print u"target word={0}, target synonym '{1}'".format(target_word, target_synonym) 
        if target_synonym != target_word:
            print "target word: " + target_word
            print "Synonyms of words in sentence: " + u', '.join(synonyms)
            print "Words in sentence without target word: " + u', '.join(sentence_minus_target)
            print u"{0} {1}  target word:'{2}', synonym found:'{3}', similarity={4}".format(i, ', '.join(words_with_syn), target_word, target_synonym, arr_target_synonyms[0][1]) 
        # print u'{0} {1}'.format(arr_target_synonyms[0][0], arr_target_synonyms[0][1])

        # sys.exit("\nLet's stop and think.")
        
print u'{0} sentences were generated by synonyms substitutions.'.format(generated_sentences_counter)



