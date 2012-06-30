<?php
//		Configuration		//
$file="./optimized.xml";
$oldfile="./info.xml"; // File to write the new optimized XML (php://stdout for standard output)
$arcade=false; // Replace the selection numbers on each page with keys that exist on an arcade controller
//$ppage=46; // Number of games to show per page

//		Arcade buttons		//
$keys=array("1","2","5","w","a","s","d","z","x","n","m","c","v",",",".");
$keys_assoc=array(0=>"w",1=>"a",2=>"s",3=>"d",4=>"z",5=>"x",6=>"1",7=>"2",8=>"5",9=>"n",10=>"m",11=>"c",12=>"v",13=>",",14=>".",);
$labels=array("1"=>"1P\t","2"=>"2P\t","5"=>"Coin","w"=>"2P Up","a"=>"2P Left","s"=>"2P Down","d"=>"2P Right","z"=>"1P a",
	"x"=>"1P b","n"=>"2P a","m"=>"2P b","c"=>"1P c","v"=>"1P d",","=>"2P c","."=>"2P d");

//		Declare required vars	//
define("ESC",27);
define("ENTER", 13);
$num=0;
$i=0;
$help=false;
$curr=0;
if(file_exists($file))
	$optimize=false;
else
	$optimize=true;

//		Initialise NCurses		//
$curse['init']=ncurses_init();
ncurses_getmaxyx(STDSCR,$curse['row'],$curse['col']);

$curse['main']=ncurses_newwin(($curse['row']/10)*9,0,0,0);
$curse['comm']=ncurses_newwin(0,$curse['col']/2,($curse['row']/10)*9,0);
$curse['search']=ncurses_newwin(0,$curse['col']/2,($curse['row']/10)*9,$curse['col']/2);
ncurses_getmaxyx($curse['main'],$curse['mrow'],$curse['mcol']);
$ppage=$curse['mrow']-2;
ncurses_refresh();

//		Start optimization	//
if($optimize) {
	$opt=fopen($file,"w");
	fwrite($opt,"<?xml version=\"1.0\"?>\n<games>\n");
	$xml=new SimpleXMLElement($oldfile,null,true);
} else
	$xml=new SimpleXMLElement($file,null,true);

//		Read the games		//
foreach($xml->game as $ngame) {
	if($optimize) { // Write all games to XML
		$attr=$ngame->attributes();
		$input=$ngame->input->attributes();
		$status=$ngame->driver->attributes();
		$desc=str_replace("&","",$ngame->description);
		$desc=str_replace("<","",$desc);
		$desc=str_replace(">","",$desc);
		fwrite($opt,"\t<game>\n\t\t<name>".trim($attr->name)."</name>\n\t\t<desc>".$desc."</desc>\n\t\t<year>".
			trim($ngame->year)."</year>\n\t\t<players>".$input->players."</players>\n\t\t<buttons>".
			$input->buttons."</buttons>\n\t\t<status>".$status->savestate."</status>\n\t</game>\n");
	} else {
		$list[$num]=trim($ngame->desc).":".trim($ngame->name);
		$games[$num]=explode(":",$list[$num]);
	}
	$num++;
}

//		Start optimization	//
if($optimize) {
	fwrite($opt,"</games>");
	fclose($opt);
	echo "Optimized file saved as $file!\n";
	exit();
}
sort($games);
sort($list);
//echo $clear;

while(1) {
	if($i<0)
		$i=0;
	ncurses_wclear($curse['main']);
	ncurses_wborder($curse['main'],0,0,0,0,0,0,0,0);
	ncurses_wattron($curse['main'],NCURSES_A_DIM);
	ncurses_mvwaddstr($curse['main'],0,$curse['col']/2,"AdvMami v2.0.0b2");
	ncurses_wattroff($curse['main'],NCURSES_A_DIM);

	ncurses_wborder($curse['comm'],0,0,0,0,0,0,0,0);
	ncurses_wborder($curse['search'],0,0,0,0,0,0,0,0);

	if(!$help) {
		for($n=0;$n<$ppage;$n++) {
			$now[$n]=$games[$i][1];
			$str="$i\t\t".$games[$i][0]." @".$games[$i][1];
			if($curr==$n) {
				ncurses_wattron($curse['main'],NCURSES_A_REVERSE);
				ncurses_mvwaddstr($curse['main'],$n+1,5,$str);
				ncurses_wattroff($curse['main'],NCURSES_A_REVERSE);
			} else {
				ncurses_mvwaddstr($curse['main'],$n+1,5,$str);
			}
			$i++;
		}
	}
	ncurses_wrefresh($curse['main']);
	ncurses_wrefresh($curse['comm']);
	ncurses_wrefresh($curse['search']);

	$in=ncurses_getch();
	echo $in;
	$br=$in==NULL?true:false;

	// Check the input for possible commands
	if(!$br) {
		switch($in) {
			case "w":
			case NCURSES_KEY_UP:
				$curr--;
				if($curr<0) {
					$curr=$ppage-1;
					$i=$i-$ppage*2;
				} else
					$i=$i-$ppage;
				$br=true;
				break;
			case "s":
			case NCURSES_KEY_DOWN:
				$curr++;
				if($curr>=$ppage)
					$curr=0;
				else
					$i=$i-$ppage;
				$br=true;
				break;
			case "a":
			case NCURSES_KEY_LEFT:
				$i=$i-$ppage*2;
				$br=true;
				break;
			case NCURSES_KEY_NPAGE:
				$i=$i+$ppage*9;
				$br=true;
				break;
			case NCURSES_KEY_PPAGE:
				$i=$i-$ppage*11;
				$br=true;
				break;
			case "q":
			case "Q":
			case ESC:
				ncurses_end();
				exit();
				break;
			case "m":
			case "x":
			case ENTER:
				startg($now[$curr]);
				$i=$i-$ppage;
				$br=true;
				break;
			case "d":
			case NCURSES_KEY_RIGHT:
				$br=true;
				break;
			case "h":
			case "?":
				echo "advMAME terminal interface by Dimitris Zervas (ttouch)\n";
				echo "The interface is writen in PHP\n";
				echo "\n";
				echo "Commands:\n";
				echo "\t<number>\t\t:The ID of the game in the current page\n";
				echo "\t#<id>\t\t\t:The unique ID of the game (shown on the left of each game)\n";
				echo "\t><page>\t\t\t:Jump to <page> page\n";
				echo "\t@<description or name>\t:The description or the rom name of the game\n";
				echo "\t&<char>\t\t:Show game which start with this character";
				echo "\thelp\t\t\t:Show this help message\n";
				echo "\t?\t\t\t:Same with help\n";
				echo "\texit\t\t\t:Just exit\n";
				echo "\tq\t\t\t:Same with exit\n";
				$help=true;
				$br=true;
				break;
		}
	}
	if($arcade && in_array("$in",$keys) && !$br) {
		foreach($keys_assoc as $id=>$key) {
			if($in==$key) {
				echo "advmame ".$now[$id]."\n";
				system("advmame ".$now[$id]);
				$i=$i-$ppage;
				$br=true;
				break;
			}
		}
	} elseif(!$arcade && (int)$in<$ppage && !strstr($in,"#") && !strstr($in,"@") && !strstr($in,">") && !strstr($in,"&") && !$br) {
		$in=(int)$in;
		echo "advmame ".$now[$in]."\n";
		system("advmame ".$now[$in]);
		$i=$i-$ppage;
		$br=true;
	} elseif(strstr($in,"&")) {
		$in=str_replace("&","",$in);
		foreach($list as $id=>$nade) {
			if(strtolower(substr($games[$id][0],0,1))==strtolower($in)) {
				$i=$id;
				$br=true;
				break;
			}
		}
	} elseif(strstr($in,"#") || strstr($in,"@") || strstr($in,">") && !$br) {
		if(strstr($in,">")) {
			$in=(int) str_replace(">","",$in);
			$i=($ppage*$in)-$ppage;
			$br=true;
		} else {
			foreach($list as $id=>$nade) {
				if($in=="#".$id || strstr($in,"@".$games[$id][0]) || $in=="@".$games[$id][1]) {
					echo "advmame ".$games[$id][1]."\n";
					system("advmame ".$games[$id][1]);
					$i=$i-$ppage;
					$br=true;
					break;
				}
			}
			if(!$br) echo "Wrong game name!";
		}
	}
}

function startg($game) {
	system("advmame ".$game);
}

ncurses_end();
?>
