fin = open("main.in", "r")
fout = open("main.out", "w")

contents = fin.read()
fout.write(contents)

fin.close()
fout.close()
