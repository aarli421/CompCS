import java.util.*;
import java.io.*;
public class gymnastics {
    public static void main(String[] args) throws IOException {
        Scanner s = new Scanner(new File("gymnastics.in"));
        PrintWriter pw = new PrintWriter(new FileWriter("gymnastics.out"));

        int sessions = s.nextInt();
        int cows = s.nextInt();
        int[][] sessionCowMatrix = new int[sessions][cows];

        for (int i = 0; i < sessions; i++) {
            for (int j = 0; j < cows; j++) {
                sessionCowMatrix[i][j] = s.nextInt();
            }
        }

        int pairCount = 0;
        for (int i = 1; i <= cows; i++) {
            for (int j = 1; j <= cows; j++) {
                if (i == j) {
                    continue;
                }

                boolean before = true;
                for (int sess = 0; sess < sessions; sess++) {
                    for (int k = 0; k < cows; k++) {
                        if (sessionCowMatrix[sess][k] == i) {
                            break;
                        }

                        if (sessionCowMatrix[sess][k] == j) {
                            before = false;
                            break;
                        }
                    }

                    if (!before) {
                        break;
                    }
                }

                if (before) {
                    pairCount++;
                }
            }
        }

        pw.println(pairCount);
        pw.close();
    }
}
