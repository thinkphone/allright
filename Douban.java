import java.io.File;
import java.io.IOException;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Set;

import org.apache.commons.io.FileUtils;
import org.json.JSONArray;
import org.json.JSONObject;

public class Douban {

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		// TODO Auto-generated method stub
		HashSet<String> cache = new HashSet<String>();
		StringBuffer bat = new StringBuffer();
		StringBuffer lst = new StringBuffer();
		Long stime = System.currentTimeMillis();

		for (int i = 0; i < 2000; i++) {
			try {
				JSONArray songs = new JSONObject(
						Utils.fileGetContent("http://douban.fm/j/mine/playlist?type=n&h=&channel=dj&pid=l3"))
						.getJSONArray("song");
				for (int j = 0; j < songs.length(); j++) {
					String artist = songs.getJSONObject(j).get("artist")
							.toString();
					String title = songs.getJSONObject(j).get("title")
							.toString();
					String url = songs.getJSONObject(j).get("url").toString();

					String oldname = url.substring(url.lastIndexOf('/') + 1);
					if (cache.add(oldname)) {
						lst.append(url + "\n");
						bat.append("rename \""
								+ oldname
								+ "\" \""
								+ (artist + "-" + title).replace("|", "")
										.replace(">", "").replace("<", "")
										.replace("\"", "").replace("?", "")
										.replace("*", "").replace(":", "")
										.replace("\\", "").replace("/", "")
								+ ".mp3\"\r\n");
					}

				}
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
		System.out.println(System.currentTimeMillis() - stime + " ms used."
				+ cache.size());
		try {
			FileUtils.write(new File("songs.lst"), lst.toString());
			FileUtils.write(new File("rename.bat"), bat.toString());
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

}
