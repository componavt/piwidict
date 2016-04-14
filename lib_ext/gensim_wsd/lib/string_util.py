#!/usr/bin/python

# Joins words in Unicode Utf8 format.
def joinUtf8( glue, word_list ):
    
    if len(word_list) == 0:
        return ""
    
    result_str = ""
    my_glue = ""
    for w in word_list:
        #w.decode('utf8') in vocab:
        #print "joinUtf8, word: '{}'".format( w )
        
        result_str += my_glue + w
        my_glue = glue
        
    return result_str;


# http://stackoverflow.com/a/27205998/1173350
def makeUnicodeString(input):
    if type(input) != unicode:
        input =  input.decode('utf-8')
        return input
    else:
        return input