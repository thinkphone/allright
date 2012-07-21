import java.io.BufferedReader;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

import javax.script.ScriptEngine;
import javax.script.ScriptEngineManager;
import javax.script.ScriptException;

/**
 * 一个下载豆瓣音乐的脚本。直接输入列表url即可
 * 
 * @author email@liuguangfeng.cn
 * 
 */
public class Douban {

	private InputStream getInputStream(String url)
			throws MalformedURLException, IOException {
		URLConnection con = new URL(url).openConnection();
		con.setConnectTimeout(5000);
		con.setReadTimeout(30000);
		con.addRequestProperty("User-Agent",
				"Mozilla/5.0 (Windows NT 6.1; rv:10.0) Gecko/20100101 Firefox/10.0");
		con.addRequestProperty("Referer", "	http://douban.fm");
		return con.getInputStream();

	}

	private String getString(String url) {
		StringBuffer sb = new StringBuffer();
		BufferedReader br = null;
		InputStream in = null;
		try {
			in = getInputStream(url);
			br = new BufferedReader(new InputStreamReader(in, "utf-8"));
			String line = null;
			while ((line = br.readLine()) != null)
				sb.append(line).append("\n");

		} catch (Exception e) {
			e.printStackTrace();
		} finally {
			if (br != null)
				try {
					br.close();
				} catch (IOException e) {
					e.printStackTrace();
				}
			if (in != null)
				try {
					in.close();
				} catch (IOException e) {
					e.printStackTrace();
				}
		}

		return sb.toString();
	}

	/**
	 * 下载一个mp3(二进制)文件到本地文件
	 * 
	 * @param url
	 * @param fn
	 */
	private void downloadMp3(String url, String fn) {
		BufferedReader br = null;
		InputStream in = null;
		OutputStream out = null;
		try {
			System.out.println("0%\t" + url + "-->" + fn);
			in = getInputStream(url);
			/* for a cache* */
			ByteArrayOutputStream ba = new ByteArrayOutputStream();
			int b = -1;
			while ((b = in.read()) != -1)
				ba.write(b);
			out = new FileOutputStream(new File(fn));
			out.write(ba.toByteArray());
			ba.close();
			System.out.println("100%\t" + url + "-->" + fn);
		} catch (Exception e) {
			e.printStackTrace();
		} finally {
			if (br != null)
				try {
					br.close();
				} catch (IOException e) {
					e.printStackTrace();
				}
			if (in != null)
				try {
					in.close();
				} catch (IOException e) {
					e.printStackTrace();
				}
			if (out != null)
				try {
					out.close();
				} catch (IOException e) {
					e.printStackTrace();
				}
		}

	}

	private static void printUsage() {
		System.out
				.println("a script to download mp3 from douban.fm\njava Douban -t<threadnum> -d<dir to save mp3> -c<maxcount> -h<show the help> -u<music list>");
		System.exit(0);
	}

	public static void main(String[] args) {

		int thread = 20;
		String _dir = "E:\\豆瓣音乐\\欧美\\";
		int maxcount = 1000;
		String _url = "http://douban.fm/j/mine/playlist?type=n&channel=2&from=mainsite&r=a09822fe44";
		for (String arg : args) {
			if (arg.startsWith("-t")) {
				try {
					thread = Integer.parseInt(arg.substring(2));
				} catch (Exception e) {
					System.err.println("thread must be a number,eg,-t20");
					printUsage();
				}
			} else if (arg.startsWith("-d")) {
				_dir = arg.substring(2);
				if (!new File(_dir).isDirectory()) {
					System.err.println(_dir + " must be a directory,check it");
					printUsage();
				}
			} else if (arg.startsWith("-c")) {
				try {
					maxcount = Integer.parseInt(arg.substring(2));
				} catch (Exception e) {
					System.err.println("maxcount be a number,eg,-c1000");
					printUsage();
				}
			} else if (arg.startsWith("-u")) {
				_url = arg.substring(2);
			} else
				printUsage();
		}
		final String dir = _dir;
		final String url = _url;
		final Douban douban = new Douban();
		final HashSet<String> urlCache = new HashSet<String>();
		/* easy way to parse JSON* */
		ScriptEngineManager sem = new ScriptEngineManager();
		final ScriptEngine se = sem.getEngineByName("javascript");
		ExecutorService e = Executors.newFixedThreadPool(thread);

		for (int i = 0; i < maxcount; i++) {
			final int j = i;
			e.execute(new Runnable() {

				@Override
				public void run() {
					System.out.println("Thread-" + j + " starting....");
					String pl = douban.getString(url);
					String script = null;
					try {
						HashMap<String, String> songs = new HashMap<String, String>();
						se.put("songs", songs);
						script = "var temp=eval("
								+ pl.trim()
								+ ");\ntemp=temp.song;\nif(typeof(temp)!=undefined)\nfor(var i in temp)\nsongs.put(temp[i].url,temp[i].artist+'-'+temp[i].title+'.mp3')";
						se.eval(script);
						System.out.println("Thread-" + j + " songs: "
								+ songs.size());
						for (String songurl : songs.keySet()) {
							if (!urlCache.contains(songurl)) {
								synchronized (urlCache) {
									urlCache.add(songurl);
								}
								douban.downloadMp3(
										songurl,
										dir
												+ songs.get(songurl)
														.replace('\\', ' ')
														.replace('/', ' ')
														.replace(':', ' ')
														.replace('*', ' ')
														.replace('?', ' ')
														.replace('"', ' ')
														.replace('<', ' ')
														.replace('|', ' ')
														.replace('>', ' '));
							} else
								System.err.println(songurl + " ingnore!");
						}
					} catch (ScriptException e) {
						e.printStackTrace();
						System.err.println(script);
					}

					System.out.println("Thread-" + j + " stoped....");
				}
			});
		}
		e.shutdown();
		try {
			e.awaitTermination(30000 * maxcount, TimeUnit.SECONDS);
			e.shutdownNow();
		} catch (InterruptedException e1) {
			e1.printStackTrace();
			e.shutdownNow();
		}
		System.out.println("good finished!");

	}
}