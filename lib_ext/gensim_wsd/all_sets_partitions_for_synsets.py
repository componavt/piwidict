#!/usr/bin/env python
# -*- coding: utf-8 -*-

import logging
import filter_vocab_words
import sys
import string_util
import codecs
import operator
import collections
import synset

logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)

from gensim.models import Word2Vec

# run:
# 
#model_name = "ruscorpora"
model_name = "news"

model = Word2Vec.load_word2vec_format("/data/all/soft_new/linguistics/rusvectores/ruscorpora.model.bin", binary=True) # hasee
#model = Word2Vec.load_word2vec_format("/media/data/all/soft_new/linguistics/rusvectores/" + model_name + ".model.bin", binary=True) # home

arg_len = len(sys.argv)
if arg_len is not 3:
    sys.exit("Error in the number of parameters. You should pass input file name and output!")
    

print ("Read data from the file: %s" % str(sys.argv[1]))
print ("Write data to the file: %s" % str(sys.argv[2]))

# Input file peculiarities
# *) let's first element in synset is a headword
# *) every line is a set of synonym words (synset)

# read file with synsets
#file_in = open('./data/in_synset.txt', 'r')

#file_in = codecs.open('./data/in_synset.txt', encoding='utf-8')
#file_in = codecs.open('./data/synset_all_relations_verb100.txt', encoding='utf-8')
file_in  = codecs.open( sys.argv[1], encoding='utf-8')
file_out = codecs.open( sys.argv[2], 'w', encoding='utf-8')

file_out.write( "Word2vec model: {}\n".format(model_name))

#for line in file_in:
#    print repr(line)

synset_dict = dict()  

# every line is a set of synonym words (synset)
for line in file_in:
    file_out.write("\n")
    arr_synset = []
    for word in line.split():
        arr_synset.append(word)
    
    arr_synset = filter_vocab_words.filterVocabWords( arr_synset, model.vocab )
    #print string_util.joinUtf8( ",", arr_synset )                              # after filter, now there are only words with vectors
    
    # synset is ready
    synset_size = len(arr_synset)
    #print "synset_size = {}".format( synset_size )
    
    if synset_size < 3:
        continue        # it is possible calculate sim0, sim1 and sim2 only if there are three or more synonyms
    
    current_synset  = synset.Synset()
    current_synset.headword = arr_synset[0] # let's first element in synset is a headword
    current_synset.line     = line
    

    syn_rank       = dict()  # integer
    syn_centrality = dict()  # float
    syn_internal   = dict()  # boolean (true for IntS, for synonyms, which always make subsets more nearer)
    
    # let's take all subsets for every 'out' element
    i=0
    while (i < synset_size):
        gr = arr_synset[:]
        # extract the element 'out' which is under consideration
        test_word = gr.pop(i)
        test_word_counter_int   = 0
        test_word_counter_float = 0
        
        sim12_greater_sim0_always = True
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
                #print "sim0 = {:5.3f}".format( sim0 )
                #print "sim1 = {:5.3f}".format( sim1 )
                #print "sim2 = {:5.3f}".format( sim2 )
                
                if sim0 > sim1 or sim0 > sim2:
                    sim12_greater_sim0_always = False
                a = 1 if sim1 > sim0 else -1
                b = 1 if sim2 > sim0 else -1
                test_word_counter_int += (a + b)/2
                test_word_counter_float += (sim1 - sim0) + (sim2 - sim0)
                
                #print "test_word_counter_int = {}".format( test_word_counter_int )
                #print "test_word_counter_float = {}".format( test_word_counter_float )
                
            #print ("---")
        syn_rank      [test_word] = test_word_counter_int;
        syn_centrality[test_word] = test_word_counter_float;
        syn_internal  [test_word] = sim12_greater_sim0_always;
        
        #print ("+++++++")
        #print
        i += 1

    
    
    #sorted_synset_rank       = collections.OrderedDict(sorted(synset_rank      .iteritems(), key=lambda x: x[1]))
    sorted_synset_centrality = collections.OrderedDict(sorted(syn_centrality.iteritems(), key=lambda x: x[1]))
    
    #sorted(synset_centrality.items(), key=operator.itemgetter(1))
    
    # print synonyms in synset with characteristics (rank, degree of centrality, belong or not to IntS)
    ints_words = []
    outs_words = []
    for key, value in sorted_synset_centrality.iteritems():
        
        int_s = 'IntS' if syn_internal [key] else ''
        #print u"{:5.2f} {:3} {} {}".format( value, syn_rank [key], key, int_s)
        file_out.write( u"{:5.2f} {:3} {} {}\n".format( value, syn_rank [key], key, int_s) )
        
        # generates two sublists of this synset: internal words (IntS) and the edge of the synset (OutS)
        if syn_internal [key]: 
            ints_words.append(key)
        else:
            outs_words.append(key)
        
    # syset properties (number of synonyms |synset|, |IntS| )
    # ints_len - number of internal synonyms in synset
    ints_len = 0
    for key, value in syn_internal.iteritems():
        if value:
            ints_len += 1
            
    current_synset.ints_len = ints_len
    current_synset.len = synset_size
    
    synset_dict [current_synset.line] = current_synset
    current_synset.ints_words = ints_words
    current_synset.outs_words = outs_words
    
    #print u"Synset len={}, |IntS|={}".format( synset_size, ints_len)
    file_out.write( u"Synset len={}, |IntS|={}\n".format( synset_size, ints_len) )
    

file_out.write("\n\n\n")

for _synset in (sorted(synset_dict.values(), key=operator.attrgetter('ints_len'))):
    
    ints_words = _synset.ints_words
    outs_words = _synset.outs_words
    
    str_ints = ""
    str_outs = ""
    if len(ints_words) > 0:
        str_ints = " IntS(" + string_util.joinUtf8( " ", ints_words ) + ") "
        
    if len(outs_words) > 0:
        str_outs = " OutS(" + string_util.joinUtf8( " ", outs_words ) + ") "
    
    file_out.write( u"{}/{} = |IntS|/|S|, [[{}]], {}{}\n".format( _synset.ints_len, _synset.len, _synset.headword, str_ints, str_outs) )
    #print u"{}/{} = |IntS|/|S|, [[{}]], {}{}".format( _synset.ints_len, _synset.len, _synset.headword, str_ints, str_outs)
    #print u"{}/{} = |IntS|/|S|, [[{}]], {}".format( _synset.ints_len, _synset.len, _synset.headword, _synset.line)
    

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