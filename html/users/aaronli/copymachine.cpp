#include <iostream>
using namespace std;

int N;

int main() {
    freopen("copymachine.in", "r", stdin);
    freopen("copymachine.out", "w", stdout);
    ios_base::sync_with_stdio(false);
    cin.tie(NULL);

//    cout << 2 << endl;

    cin >> N;
    for (int i = 0; i < N; i++) {
        if (i < N) {
            int x;
            cin >> x;
            cout << x << endl;
        }
    }

    return 0;
}