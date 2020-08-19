import java.util.*;
import java.io.*;

public class copymachine{
	public static void main(String[] args) throws IOException{
		BufferedReader br = new BufferedReader(new FileReader("copymachine.in"));
		PrintWriter pw = new PrintWriter(new BufferedWriter(new FileWriter("copymachine.out")));
		StringTokenizer st = new StringTokenizer(br.readLine());
		int N = Integer.parseInt(st.nextToken());
		for(int i = 0; i < N; i++){
			StringTokenizer gt = new StringTokenizer(br.readLine());
			pw.println(Integer.parseInt(gt.nextToken()));
		}
		pw.close();
	}
}