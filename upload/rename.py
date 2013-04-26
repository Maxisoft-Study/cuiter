#!/usr/bin/python
# -*- coding: UTF-8 -*-

import glob
import os

XOR_MASTER_PASSW = 745817828

for f_name in glob.glob('*.jpg'):
	f_int = int(f_name.split('.')[0])
	os.rename(f_name, f_name.replace(str(f_int), str(f_int ^ XOR_MASTER_PASSW)))