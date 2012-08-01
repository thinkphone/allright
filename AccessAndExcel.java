import java.io.PrintWriter;
import java.sql.Connection;
import java.sql.DatabaseMetaData;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.Statement;

public class AccessAndExcel {
	public static void main(String[] args) {
		try {
			Class.forName("sun.jdbc.odbc.JdbcOdbcDriver");
			String url = "jdbc:odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};DBQ=z:/qq/a.accdb";

			Connection con = DriverManager.getConnection(url);
			System.out.println("Connected!");
			Statement st = con.createStatement();
			DatabaseMetaData d = con.getMetaData();
			// 列出所有表。
			ResultSet r = d.getTables(null, null, "%", null);
			while (r.next())
				System.out.println(r.getString(3));

			// 处理某个表。
			String table = "有效询盘";
			StringBuffer sb = new StringBuffer();
			// 列出表头
			r = d.getColumns(null, null, table, null);
			int colums = 1;
			while (r.next()) {
				colums++;
				sb.append("`" + r.getString("COLUMN_NAME") + "` varchar(255),");
			}
			System.out.println(sb);
			PrintWriter pw = new PrintWriter(table + ".colums.txt", "UTF-8");
			pw.write(sb.toString());
			pw.close();
			// 列出数据。
			sb = new StringBuffer();
			r = st.executeQuery("select * from " + table);
			int i = 0;
			pw = new PrintWriter(table + ".data.txt", "UTF-8");
			while (r.next()) {
				if (i++ % 5000 == 0)
					System.out.println(i);
				if (i % 50000 == 0) {
					pw.print(sb.toString());
					pw.flush();
					sb = new StringBuffer();
				}
				StringBuffer temp = new StringBuffer();
				for (int j = 1; j < colums; j++) {
					temp.append("\t" + r.getString(j).trim().replace('\t', ' '));
				}
				sb.append(temp.substring(1) + "\n");

			}
			con.close();
			pw.write(sb.toString());
			pw.close();
		} catch (Exception e) {
			e.printStackTrace();
		}

	}

}
