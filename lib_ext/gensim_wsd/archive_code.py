#!/usr/bin/env python
# -*- coding: utf-8 -*-

# tutti-frutti of code, which could be useful oneday

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

#arg_len = len(sys.argv)
#if arg_len is not 3:
#    sys.exit("Error in the number of parameters. You should pass input file name and output!")
    
#print ("Read data from the file: %s" % str(sys.argv[1]))
#print ("Write data to the file: %s" % str(sys.argv[2]))

# Input file peculiarities
# *) let's first element in synset is a headword
# *) every line is a set of synonym words (synset)


#sim1 = model.similarity(u'снискивать', u'стяжать')
#print "distance('снискивать', 'стяжать') = {}".format(sim1)

print
#         absent in model                             presented
#source_words = [u'убаюкивать', u'укачивать', u'усыплять', u'бронь']
#source_words = [u'доносить', u'осведомлять', u'докладывать', u'объявлять', u'заявлять', u'предупреждать', u'извещать', u'информировать', u'сообщать', u'уведомлять', u'оповещать']

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

sys.exit("End.")




#sim2 = model.n_similarity([u'убаюкивать', u'укачивать', u'усыплять', u'бронь'], [u'пробуждать', u'усыплять', u'бронь'])
#print "With    word 'бронь' in both lists sim={}".format( sim2 )

sim3 = model.n_similarity([u'убаюкивать', u'укачивать', u'усыплять', u'бронь'], [u'пробуждать', u'усыплять'])
print "With    word 'бронь' in the first list sim={}".format( sim3 )

sim4 = model.n_similarity([u'убаюкивать', u'укачивать', u'усыплять'], [u'пробуждать', u'усыплять', u'бронь'])
print "With    word 'бронь' in the second list sim={}".format( sim4 )
print

print
print "'баюкать 1.' (+syn: убаюкивать; +hyper: укачивать, усыплять) similarity to 'бронь 2':  (+syn: возбуждать, вызывать, пробуждать):"

sim1 = model.n_similarity([u'убаюкивать', u'укачивать', u'усыплять'], [u'возбуждать', u'вызывать', u'пробуждать'])
#sim1 = model.n_similarity([u'кормить', u'содержать', u'заботиться'], [u'кинуть', u'запустить', u'швырнуть'])
print "Without word 'бронь' in both lists sim={}".format( sim1 )

#sim2 = model.n_similarity([u'убаюкивать', u'укачивать', u'усыплять', u'бронь'], [u'возбуждать', u'вызывать', u'пробуждать', u'бронь'])
#print "With    word 'бронь' in both lists sim={}".format( sim2 )

sim3 = model.n_similarity([u'убаюкивать', u'укачивать', u'усыплять', u'бронь'], [u'возбуждать', u'вызывать', u'пробуждать'])
print "With    word 'бронь' in the first list sim={}".format( sim3 )

sim4 = model.n_similarity([u'убаюкивать', u'укачивать', u'усыплять'], [u'возбуждать', u'вызывать', u'пробуждать', u'бронь'])
print "With    word 'бронь' in the second list sim={}".format( sim4 )
print

#sim1 = model.n_similarity([u'потухать', u'тухнуть'], [u'ослабевать'])
#print "Without antonyms sim={}".format( sim1 )

#sim2 = model.n_similarity([u'потухать', u'тухнуть'], [u'ослабевать', u'усиливаться', u'крепнуть', u'возобновляться', u'появляться'])
#print "With    antonyms sim={}".format( sim2 )
#print

# topn=10, 
#similar_words = model.most_similar(positive=[u'женщина', u'король'], negative=[u'мужчина'], topn=30 )

#for i in range(len(similar_words)):
#    print "{}. dist={}, {}".format( i+1, similar_words[i][1], similar_words[i][0].encode('utf8'))


#print "+ (спец, специалист, профи, профессионал, мастер, мастак, правитель, монарх) - (королева, нищий):"
#similar_words = model.most_similar(positive=[u'спец', u'специалист', u'профи', u'профессионал', u'мастер', u'мастак', u'правитель', u'монарх'], negative=[u'королева', u'нищий'], topn=30 )
#similar_words = model.most_similar(positive=[u'спец', u'специалист', u'профи', u'профессионал', u'мастер', u'мастак', u'правитель', u'монарх'], negative=[], topn=30 )
#similar_words = model.most_similar(positive=[u'правитель', u'монарх'], negative=[u'королева', u'нищий'], topn=30 )

#similar_words = model.most_similar_cosmul(positive=[u'правитель', u'монарх'], negative=[u'королева', u'нищий'], topn=30 )
#similar_words = model.most_similar_cosmul(positive=[u'спец', u'специалист', u'профи', u'профессионал', u'мастер', u'мастак', u'правитель', u'монарх'], negative=[u'королева', u'нищий'], topn=30 )
#similar_words = model.most_similar_cosmul(positive=[u'монарх', u'карта', u'фигура', u'лидер'], negative=[], topn=30 )