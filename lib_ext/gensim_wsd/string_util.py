#!/usr/bin/python

# Joins words in Unicode Utf8 format.
def joinUtf8( glue, word_list ):
    
    result_str = ""
    for w in word_list:
        #w.decode('utf8') in vocab:
        #print "joinUtf8, word: '{}'".format( w )
        result_str += w
        result_str += glue
    return result_str;


# http://stackoverflow.com/a/27205998/1173350
def makeUnicodeString(input):
    if type(input) != unicode:
        input =  input.decode('utf-8')
        return input
    else:
        return input