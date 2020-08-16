
import java.util.*;
import java.io.*;
public class whereami {
	public static void main(String[] args) throws IOException {
		BufferedReader br = new BufferedReader(new FileReader("whereami.in"));
		//BufferedReader br = new BufferedReader(new FileReader("D:\\javastuff\\eclipse\\USACO1\\src\\USACO1\\whereami.txt"));
	        //BufferedReader br = new BufferedReader(new InputStreamReader(System.in));
		PrintWriter pw = new PrintWriter(new BufferedWriter(new FileWriter("whereami.out")));

		StringTokenizer st = new StringTokenizer(br.readLine());
		int N = Integer.parseInt(st.nextToken());
		int[] letters = new int[27];
		int[] letters2 = new int[27];
		String sequences = "";
		int counter = 0;
		StringBuilder builder = new StringBuilder(sequences);
		StringTokenizer gt = new StringTokenizer(br.readLine());

		String A = gt.nextToken();
		builder.append(A);

		System.out.println(builder);
		List<String> index = new ArrayList<>();

		for(int i = 0; i < builder.length(); i++) {

			letters[builder.charAt(i) - 64]++;
			//index.add(builder.charAt(i) - 64);

		}
		System.out.println(Arrays.toString(letters));
		//sliding window
		int left = 0;
		int right = 0;
		char othertemp = Character.MIN_VALUE;
		char temp = builder.charAt(right);
		int rightTemp = 0;
		int least = Integer.MAX_VALUE;
		boolean secondgo = true;
		boolean go = true;
		int counterLoop = 0;
		while(right < builder.length()) {
			
			if(go) {
				right++;
				if(builder.charAt(right) == temp) {
					temp += othertemp;
					right--;
					othertemp = Character.MIN_VALUE;
					go = false;
				} else {
					othertemp += builder.charAt(right);
				}
			} else {
				
				if(secondgo){
					rightTemp = right;
					secondgo = false;
				}
				
				index.add(builder.substring(left, right + 1));
				left++;
				right++;
				System.out.println(left + " " + right);
				if(index.contains(builder.substring(left, right + 1)) && counter == 0) {
					counter++;
					int tempRight = (right - left) + 1;
					right = right - tempRight;
					left = 0;
					secondgo = true;
					//System.out.println(right);
					
				} else if(index.contains(builder.substring(left, right + 1)) && counter == 0) {
					right++;
					break;
				} else if(index.contains(builder.substring(left, right + 1)) && counter == 1) {
					System.out.println(builder.substring(left, right + 1));
					right++;
					break;
				}
				else if(builder.length() - 1 == right) {
					
					right = rightTemp - 1;
					left = 0;
					index.clear();
					secondgo = true;
				} 
			}	
			
		}
		pw.println(right - left + 1);
		pw.close();		
	}
}
