import java.util.*;
import java.io.*;

public class Main {
    public static void main(String[] args) throws IOException {
        BufferedReader br = new BufferedReader(new FileReader(new File("main.in")));
        PrintWriter pw = new PrintWriter(new FileWriter(new File("main.out")));

        int[] arr = new int[5];
        //int i = arr[6];

        if (br.readLine().equals("5")) pw.print("5");

        pw.close();
    }
}
