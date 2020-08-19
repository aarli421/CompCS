f = open("copymachine.in", "r")
p = open("copymachine.out", "w")

N = int(f.readline())
for i in range(N):
	p.write(f.readline())
f.close()
p.close()