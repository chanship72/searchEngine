import os
import html2text
import enchant
import sys
from HTMLParser import HTMLParser
import re
import urllib
from bs4 import BeautifulSoup

path = "/Users/peter.park/workspace/solr/latimes/"

outputFileObject = open("/Users/peter.park/workspace/solr/spellchecker/big.txt","w")

def visible(element):
	if element.parent.name in ['style' , 'script' , 'document' , 'head' , 'title']:
		return False
	return True


for filename in os.listdir(path):
	print(filename)
	html = open(path+filename).read()
	soup = BeautifulSoup(html, 'lxml')
	ss = soup.findAll(text = True)
	s = filter(visible, ss)
	s = u' '.join(s).encode('utf-8').strip()
	s = s.replace("'s", "")
	s = s.replace("'", "")
	lst = s.split()
	bigs =""
	d = enchant.Dict("en_US")
	for item in lst:
	 	if d.check(item):
	 		bigs += item + " "
	outputFileObject.write(bigs)
	outputFileObject.write("\n")
outputFileObject.close()
