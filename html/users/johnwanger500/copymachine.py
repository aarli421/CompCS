import linecache

N = linecache.getline("copymachine.in", 1)
x = linecache.getline("copymachine.in", 2)

N = int(N[0: -1])
x = int(x[0: -1])

def copy(num1, num2):
    return num1 + num2

result = str(copy(N, x)) + "\n"

with open("copymachine.out", "w") as myfile:
    myfile.write(result)
