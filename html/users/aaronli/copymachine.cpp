#include <iostream>
using namespace std;

int N;

int main() {
	freopen("copymachine.in", "r", stdin);
	freopen("copymachine.out", "w", stdout);
	cin >> N;
	for (int i = 0; i < N; i++) {
		int x;
		cin >> x;
		cout << x << endl;
	}
}