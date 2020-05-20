#include <iostream>
#include <fstream>
using namespace std;

int main() {
    ifstream fin("main.in");
    ofstream fout("main.out");

    string s;
    fin >> s;
    fout << s;

    return 0;
}
