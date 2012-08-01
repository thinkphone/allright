import java.util.Arrays;
import java.util.Collections;
import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;

public class DouDiZhuTest {
	public static void main(String[] args) {
		List<String> pai = Arrays.asList(new String[] { "A-1", "A-2", "A-3",
				"A-4", "2-1", "2-2", "2-3", "2-4", "3-1", "3-2", "3-3", "3-4",
				"4-1", "4-2", "4-3", "4-4", "5-1", "5-2", "5-3", "5-4", "6-1",
				"6-2", "6-3", "6-4", "7-1", "7-2", "7-3", "7-4", "8-1", "8-2",
				"8-3", "8-4", "9-1", "9-2", "9-3", "9-4", "10-1", "10-2",
				"10-3", "10-4", "J-1", "J-2", "J-3", "J-4", "Q-1", "Q-2",
				"Q-3", "Q-4", "K-1", "K-2", "K-3", "K-4", "W-1", "W-2" });
		HashMap<Character, LinkedList<String>> tong = new HashMap<Character, LinkedList<String>>();
		for (int count : new Integer[] { 100, 1000, 10000, 100000 }) {
			for (boolean isDizhu : new Boolean[] { true, false }) {
				int dui = 0;
				int san = 0;
				int zha = 0;
				int wangzha = 0;
				for (int i = 0; i < count; i++) {
					tong.clear();
					Collections.shuffle(pai);
					// 如果是地主。取54/3+3=17+3=20.不是的话就是17张。
					for (int j = 0, len = isDizhu ? 20 : 17; j < len; j++) {
						char p = pai.get(j).charAt(0);
						LinkedList<String> l = (tong.get(p) == null) ? new LinkedList<String>()
								: tong.get(p);
						l.add(pai.get(j));
						tong.put(p, l);
					}
					for (Character key : tong.keySet()) {
						switch (tong.get(key).size()) {
						case 2:
							if (tong.get(key).get(0).charAt(0) != 'W')
								dui++;
							else
								wangzha++;
							break;
						case 3:
							san++;
							break;
						case 4:
							zha++;
							break;
						default:
							break;
						}
					}
				}
				System.out.printf(
						"在%d次统计中(%s)：\n对有%d个\n三个的有%d个\n王炸有%d个\n炸弹有%d个\n",
						count, (isDizhu ? "是地主" : "不是地主"), dui, san, wangzha,
						zha);
			}
			System.out.println("\n");
		}

	}
}
