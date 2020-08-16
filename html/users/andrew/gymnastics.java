import java.util.*;
import java.io.*;

public class gymnastics {
	public static void main(String[] args) throws IOException {
		BufferedReader br = new BufferedReader(new FileReader("gymnastics.in"));
		//BufferedReader br = new BufferedReader(new FileReader("D:\\javastuff\\eclipse\\USACO_WEI\\src\\USACOWEI\\gymnastics.txt"));
		//BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
		PrintWriter pw = new PrintWriter(new BufferedWriter(new FileWriter("gymnastics.out")));

		StringTokenizer st = new StringTokenizer(br.readLine());
		int K = Integer.parseInt(st.nextToken());
		int N = Integer.parseInt(st.nextToken());
		
		int[][] betterCows = new int[N][N];
		int[][] cowStats = new int[K][N];
		
		for(int i = 0; i < K; i++) {
			StringTokenizer gt = new StringTokenizer(br.readLine());
			for(int j = 0; j < N; j++) {
				cowStats[i][j] = Integer.parseInt(gt.nextToken());
			}
		}
		int counter = 0;
		for(int i = 0; i < cowStats.length; i++) {
			int slow = 0;
			int fast = 0;
			while(slow < cowStats[i].length) {
				fast++;
				if(fast == cowStats[i].length) {
					slow++;
					fast = slow;
				} else {	
					betterCows[cowStats[i][slow] - 1][cowStats[i][fast] - 1]++;
					if(betterCows[cowStats[i][slow] - 1][cowStats[i][fast] - 1] == K) {
						counter++;
					}
				}	
			}
		}
		pw.println(counter);
		pw.close();
	}
}
