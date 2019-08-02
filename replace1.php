<?php header ('Content-type: text/html; charset=utf-8'); 
if ($_GET['p']==12345) {
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
</head>
<body style="margin: 0; padding: 0;">
<?php

		
	if (isset($_POST['submit'])) {
		
		$err_arr = array(0 => '', 1 => '');
		
		if ($_POST['text'] == '' || $_POST['dir'] == '') {
			
			$err = ' style="border: 1px solid red"';
			
			if ($_POST['text'] == '')
				$err_arr[0] = $err;
			if ($_POST['dir'] == '')
				$err_arr[1] = $err;
		} else {
		
			set_time_limit(0);
			error_reporting(E_ALL);
			
			$dir = trim($_POST['dir']);
			$text = stripslashes($_POST['text']);
			$retext = stripslashes($_POST['retext']);
			$replace = isset($_POST['replace']) ? $_POST['replace'] : 0;
			$ext = explode(',', $_POST['ext']);
			$cnt = 0;

			function scan_dir($dirname) { 
				global $text, $retext, $replace, $ext, $cnt;
				
				$dir = opendir($dirname);
				
				while (($file = readdir($dir)) !== false) {
					if ($file != "." && $file != "..") {
						$file_name = $dirname."/".$file;
						if (is_file($file_name)) {
							$ext_name = substr(strrchr($file_name, '.'), 1);
							if (in_array($ext_name, $ext) || $file_name == $dirname.'/replace.php') 
								continue;
							
							$content = file_get_contents($file_name);
							if (strpos($content, $text) !== false) {
								$cnt++;
								if ($replace) {
									$content = str_replace($text, $retext, $content); 
									file_put_contents($file_name, $content);
								}
								
								echo '<b>'.$cnt.'</b>: '.$file_name.'<br>';
							}
						}
		
						if (is_dir($file_name)) {
							scan_dir($file_name);
						}
					}
					
				}
				
				closedir($dir);
			}
		  
			$start_time = microtime(true);
			
			echo '<div style="padding: 10px; width: 98%; background: #FFEAEA; border: 2px solid #FFB0B0; margin-bottom: 20px">';
			
			if ($replace)
				echo '<h2>Заданный текст заменен в файлах:</h2>';
			else
				echo '<h2>Заданный текст найден в файлах:</h2> ';

			scan_dir($dir);
			
			if (!$cnt)
				echo 'Нет совпадений';
				
			$exec_time = microtime(true) - $start_time;
			
			printf("<br /><b>Время выполнения: %f сек.</b></div>", $exec_time);
		}
	}
?>
<div style="padding: 10px; width: 100%; background: #E7F0F5; border: 2px solid #C5E7F6; text-align: center;">
    <form method="post">
        <table cellpadding="5" cellspacing="0" border="0" align="center">
            <tr>
                <td align="right">
                    Текст поиска*:
                </td>
                <td>
                    <textarea<?php echo $err_arr[0]; ?> name="text" cols="25" rows="7"><?php echo isset($text) ? $text : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td align="right">
                    Текст замены:
                </td>
                <td>
                    <textarea name="retext" cols="25" rows="7"><?php echo isset($retext) ? $retext : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td align="right">
                    Замена:
                </td>
                <td>
                    <input type="checkbox"<?php echo isset($replace) && $replace == 1 ? ' checked' : ''; ?> name="replace" value="1" />
                </td>
            </tr>
            <tr>
                <td align="right">
                    Не искать в файлах:
                </td>
                <td>
                    <input type="text" size="33" name="ext" value="<?php echo isset($_POST['ext']) ? $_POST['ext'] : 'gif,jpg,jpeg,png,zip,rar,pdf,css'; ?>" />
                </td>
            </tr>
            <tr>
                <td align="right">
                    Папка*:
                </td>
                <td>
                    <input<?php echo $err_arr[1]; ?> type="text" size="33" name="dir" value="<?php echo isset($dir) ? $dir : '.'; ?>" title='Введите ".", если поиск по этой папке, иначе просто имя папки' />
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <br /><input type="submit" name="submit" value="Искать\Заменить" />
                </td>
            </tr>
        </table>
    </form>
</div>
</body>
</html>
<? } ?>