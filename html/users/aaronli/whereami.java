import java.util.*;
import java.io.*;

public class whereami {
    public static void main(String[] args) throws IOException {
        Scanner s = new Scanner(new File("whereami.in"));
        PrintWriter pw = new PrintWriter(new FileWriter("whereami.out"));

        int mailboxes = s.nextInt();
        s.nextLine();
        char[] road = s.nextLine().toCharArray();

        int leastCons = 0;
        if (mailboxes != 0) {
            for (int i = 1; i <= mailboxes; i++) {
                boolean works = true;
                for (int j = 1; j < mailboxes - i + 1; j++) {
                    char[] unique = Arrays.copyOfRange(road, j, j + i);
                    char[] afterString = Arrays.copyOfRange(road, j + i, road.length);
                    char[] beforeString = Arrays.copyOfRange(road, 0, j);

                    if (String.valueOf(beforeString).contains(String.valueOf(unique)) || String.valueOf(afterString).contains(String.valueOf(unique))) {
                        works = false;
                    }
                }

                if (works) {
                    leastCons = i;
                    break;
                }
            }
        }

        pw.println(leastCons);
        pw.close();
    }
}
