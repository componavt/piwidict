#!/usr/bin/env python
# -*- coding: utf-8 -*-

import logging
import filter_vocab_words
import sys
import string_util
import codecs
import operator
import collections

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import Word2Vec

#model = Word2Vec.load_word2vec_format("/data/all/soft_new/linguistics/rusvectores/news.model.bin", binary=True)  # C binary format
model = Word2Vec.load_word2vec_format("/data/all/soft_new/linguistics/rusvectores/ruscorpora.model.bin", binary=True) # hasee
#model = Word2Vec.load_word2vec_format("/media/data/all/soft_new/linguistics/rusvectores/ruscorpora.model.bin", binary=True) # home


# read file with synsets
#file_in = open('./data/in_synset.txt', 'r')

file_in = codecs.open('./data/in_synset.txt', encoding='utf-8')
#for line in file_in:
#    print repr(line)

# every line is a set of synonym words (synset)
for line in file_in:
    print
    synset = []
    for word in line.split():
        synset.append(word)
    
    filter_vocab_words.filterVocabWords( synset, model.vocab )
    print string_util.joinUtf8( ",", synset )                   # after filter, now there are only words with vectors
    
    
    
    # synset is ready
    synset_size = len(synset)
    #print "synset_size = {}".format( synset_size )

    synset_rank = dict()        # integer 
    synset_centrality = dict()  # float
    
    # let's take all subsets for every 'out' element
    i=0
    while (i < synset_size):
        gr = synset[:]
        # extract the element 'out' which is under consideration
        test_word = gr.pop(i)
        test_word_counter_int   = 0
        test_word_counter_float = 0

        for j in range(0,len(gr)):
            for l in range(j,len(gr)-1):
                gr1 = gr[j:l+1]
                gr2 = gr[0:j]+gr[l+1:len(gr)]
                #print u"{} | gr1={} | gr2={}".format( test_word,  string_util.joinUtf8( ",", gr1 ), 
                #                                                  string_util.joinUtf8( ",", gr2 ) )
                
                gr1_and_test_word = gr1[:]
                gr1_and_test_word.append( test_word )
                
                gr2_and_test_word = gr2[:]
                gr2_and_test_word.append( test_word )
                                                            
                sim0 = model.n_similarity(gr1, gr2)
                sim1 = model.n_similarity(gr1_and_test_word, gr2)
                sim2 = model.n_similarity(gr1,               gr2_and_test_word)
                #print "sim0 = {}".format( sim0 )
                #print "sim1 = {}".format( sim1 )
                #print "sim2 = {}".format( sim2 )
                
                a = 1 if sim1 > sim0 else -1
                b = 1 if sim2 > sim0 else -1
                test_word_counter_int += (a + b)/2
                #print "test_word_counter = {}".format( test_word_counter )
                
                test_word_counter_float += (sim1 - sim0) + (sim2 - sim0)
                
                
        #   print ("---")
        synset_rank      [test_word] = test_word_counter_int;
        synset_centrality[test_word] = test_word_counter_float;
        
        #print ("+++++++")
        i += 1

    
    
    #sorted_synset_rank       = collections.OrderedDict(sorted(synset_rank      .iteritems(), key=lambda x: x[1]))
    sorted_synset_centrality = collections.OrderedDict(sorted(synset_centrality.iteritems(), key=lambda x: x[1]))
    
    #sorted(synset_centrality.items(), key=operator.itemgetter(1))
    
    for key, value in sorted_synset_centrality.iteritems():
        print u"{:5.2f} {:3} {}".format( value, synset_rank [key], key)
sys.exit("File read. Done.")













#sim1 = model.similarity(u'снискивать', u'стяжать')
#print "distance('снискивать', 'стяжать') = {}".format(sim1)

print
#         absent in model                             presented
words = ["запустить", "бросить", "бронь", "баюкать", "швырнуть", "возбуждать", "вызывать", "пробуждать", "укачивать"] # words which are absent in models :(
#words = ["слово", "швырнуть", "бросить"] # words which are absent in models :(
for w in words:
    if w.decode('utf8') in model.vocab:
        print "The word '{}' indexed by word2vec model.".format( w )
    else:
        print "My KeyError: The word '{}' does not indexed by word2vec model.".format( w )
print


print "'баюкать 1.' (+syn: убаюкивать; +hyper: укачивать, усыплять) similarity to 'бронь 1':  (+syn: пробуждать; +antonym: усыплять):"

sim1 = model.n_similarity([u'убаюкивать', u'укачивать', u'усыплять'], [u'пробуждать', u'усыплять'])
#sim1 = model.n_similarity([u'кормить', u'содержать', u'заботиться'], [u'кинуть', u'запустить', u'швырнуть'])
print "Without word 'бронь' in both lists sim={}".format( sim1 )

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