<?PHP 

/*
 * Mbfier, the gallery bot
 * Copyright (C) 2021 Daniele Bonini
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *  
 */   

 header("Content-Type: text/javascript");

 $targetDomain = filter_input(INPUT_GET, "td", FILTER_SANITIZE_STRING);
 if (substr($targetDomain, 0, 4) == "www.") {
   $cleanTargetDomain = substr($targetDomain, 4);
 } else {
   $cleanTargetDomain = $targetDomain;
 } 
 $cleanTargetDomain = ucfirst($cleanTargetDomain);
 $ipos = mb_strripos("~" . $cleanTargetDomain, ".");
 if ($ipos) {
   $galTitle = strtoupper(substr($cleanTargetDomain, 0, $ipos-1));
 } else {
   $targetDomain = $targetDomain . ".com";
   echo("window.open('http://" . $targetDomain . ".mbfier.com','_self');");
   exit(0);
 }  


 $output1 = filter_input(INPUT_GET, "out", FILTER_SANITIZE_STRING);
 $output1 = strtolower($output1); 
 if ($output1 == "json") {
   $output = 2; // set flag for json output
 } else if ($output1 === "std") {
   $output = 1; // set flag for standard output
 } else {
   echo("out parameter error.");
   exit(0);
 }
 $verbose1 = filter_input(INPUT_GET, "v", FILTER_SANITIZE_STRING);
 if ($verbose1 == "0") {
   $verbose = 0; // set flag for defalt app verbosity
 } else if ($verbose1 === "1") {
   $verbose = 1; // set flag for quite verbosity
 } else {
   echo("verbose parameter error.");
   exit(0);
 }
 
?>

/*
 * Mbfier, the gallery bot
 * Copyright (C) 2021 Daniele Bonini
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *  
 */    

var recNum = 0;
var nImgProcessed = 0;
var output = <?PHP echo($output); ?>; 
var verbose = <?PHP echo($verbose); ?>; 
var targetDomain = "<?PHP echo($targetDomain);?>";
var galTitle = "<?PHP echo($galTitle);?>";
var landingPage = "<?PHP echo($cleanTargetDomain);?>";

var crawlres = [];
var logoFound = false;

function loadDoc() {

    recNum++;

    bConnectionOK = false;

    if (verbose===0 && recNum===1) {

      document.write("Copyrights (C) 2021 Daniele Bonini. GNU General Public License ver3<br>");
      document.write("<br>");
      document.write("This is a bit of doc:<br>");
      document.write("- out: std (for standard output) or json<br>");
      document.write("- v: 0 (for standard verbosity) or 1 (for quite output)<br>");
      document.write("<br>");
      document.write("The service is running and trying to connect to the target server.<br>");
      document.write("The connection to the target web server can eventually fail.<br>");
      document.write("Cause could be found among the following reasons:<br>");
      document.write("- Internet connection lost.<br>");
      document.write("- DNS failure.<br>");
      document.write("- Target server is down.<br>");
      document.write("- Access-Control-Allow-Origin header missing on the target server.<br>");
      document.write("<br>");

    }

    var xhttp = new XMLHttpRequest();
    xhttp.open("GET", "http://"+targetDomain+".mbfier.com/upload.php?url=http://"+targetDomain, true);  
    xhttp.send();
    xhttp.onreadystatechange = function() {

      if (this.readyState == 4 && this.status == 200) {
  
        bConnectionOK = true;

        if (verbose===0) {
          document.write("Connection succeeded.<br><br>")
        }
        
        try {
          document.getElementById("grausi").value = this.responseText;  
        }
        catch (error) {
          //console.error(error);
          if (!document.getElementById("grausi")) {
            document.write("<input id='grausi' type='hidden' value=''>");
            document.getElementById("grausi").value = this.responseText;
          }  
        } 
        finally {
          document.getElementById("grausi").addEventListener("change", launchParsing(), true);  
        }
        
      } else {

        if ((this.readyState == 4 && this.status == 0) && (!bConnectionOK)) {
          if (verbose===0) {
            document.write("Final result:<br>");
            document.write("Connection problems detected.<br><br>");
          }
        } 
      }

    }
}  

function writeHeader() {
  //TITLE BAR    
  if ((output == 1 && verbose == 1) && (nImgProcessed==0 && recNum==1)) {
    divTitle = "<div style='position:relative;margin-top:8px;top:0px;left:0px;width:100%'>";
    document.write(divTitle);
    divTitle = "<div style='height:50px;text-align:center;opacity:1.0;width:350px;clear:both;margin:auto;vertical-align:middle;border:0px solid green;background: rgba(3, 169, 245, 0.7);color:#FFFFFF;'><span style='position:relative;top:-15px;font-size:25px;font-weight:900;'><br>"+galTitle+"<br><br></span></div></div>";
    document.write(divTitle);
  }  
  // END TITLE
}

function crawlImages() {
  var bTitle = false;
  var found = "||";
  var z = 0;
  var str = document.getElementById('grausi').value;
  str = str.replace("/\n/"," ");
  //var regexp = /<img.*?src="[^*?"<>|]+".*?>/gi;
  //var regexp = /("|')([^*?"<>|]+\.(png|gif|jpg|jpeg))("|')/gi;
  //var regexp = /<img\n?.*src=("|')?([^:*?"<>|]+\.(png|gif|jpg|jpeg))("|')?.*?\n?\/?>/gis;
  //var regexp = /<img.*\n?.*src=("|')?(?!(cid:|data:))([^*?"<>|]+\.(png|gif|jpg|jpeg))("|')?.*?\n?[^*?"<>|]*\/?>/gi;
  var regexp = /<img\n?.*?\n?\/?>/gis;
  var x = str.match(regexp);
  if (x) {
    for (i=0;i<x.length;i++) {
      str2 = x[i]; //.toLowerCase();
      str3 = str2.toLowerCase();
      if (str2.length <= 4) {
        continue;
      }
      if ((str2.indexOf("blank.gif")>-1 || str2.indexOf("pixel.gif")>-1 || str2.indexOf("pxl.gif")>-1 || str2.indexOf("pix.gif")>-1) && str2.indexOf("data-src")==-1) {
        continue;
      }
      if (str2.indexOf(" src=\"data:")>-1 || str2.indexOf(" src='data:")>-1 || str2.indexOf(" src=data:")>-1) {
        str2 = str2.replace(" src=", " zzz=");
        str2 = str2.replace(" data-src=", " src=");
      }
      if (str3.indexOf("credit-card")>-1 || str3.indexOf("loyalty")>-1) {
        continue;
      }
      oo = str2.indexOf(" src=");
      ii = str2.indexOf(" data-src=");
      jj = str2.indexOf(".jpg");
      yy = str2.indexOf(".jpeg");
      kk = str2.indexOf(".png");
      ww = str2.indexOf(".gif");
      if (((oo==-1) && (oo<ii)) && ii>-1 && ((jj>ii) || (yy>ii) || (kk>ii) || (ww>ii))) {
        //alert(str2);
        //var regexp3 = /<img\n?.*data-src=("|')([^*?"<>|]+\.(png|gif|jpg|jpeg))("|').*?\n?\/?>/gis;
        //var zz = str2.match(regexp3);
        //if (zz[0]) {
          //str3 = zz[0];
          str2 = str2.replace(" src=", " zzz=");
          str2 = str2.replace(" data-src=", " src=");
          //str2 = str3;
        //}
      }  
      str2 = str2.trim();
      if (str2 !== "") {
        newurl = str2; 
        //var regexp2 = /([\w\.-]+\.[a-z\.]{2,8})/gi;
        //var regexp2 = /([\w\.-]+\.(png|jpg|jpeg))/gi;
        //var regexp2 = /src="([\w\:\/\.-]+\.(png|jpg|jpeg))"/gi;
        //var regexp2 = /<img.*?src="[^*?"<>|]+".*?>/gi;
        //var regexp2 = /("|')([^*?"<>|]+\.(png|gif|jpg|jpeg))("|')/gi;
        //var regexp2 = /<img\n?.*src=("|')([^*?"<>|]+\.(png|gif|jpg|jpeg))("|').*?>/gi;
        var regexp2 = /<img\n?.*?\n?\/?>/gis;
        
        var y = newurl.match(regexp2);

        if (y[0]) {
	        if (found.indexOf("|" + y[0] + "|") == -1) {
            crawlres[z] = y[0];
            z++;
	          found = found + "|" + y[0] + "|";
	        }
        }
      }
    }
  }

  /*
   * DUMP
   */
  dump = false;
  if (dump || (verbose == 0) || ((output==2) && (verbose == 1))) {
    for (i=0;i<crawlres.length;i++) {
      str = crawlres[i];                        
      str = str.replace("<", "&lt;");
      str = str.replace(">", "&gt;");
      crawlres[i] = str;
    }
  }
  
  if (output === 1) {

    if (verbose === 0) {
      document.write("Image list:<br>");
    }

    /*
     * PATH FIX
     */
    for (i=0;i<crawlres.length;i++) {
      str = crawlres[i];                        
      ipos = str.indexOf("src=//");
      if (ipos>-1) {
        str = str.replace("src=//", "src=http://");
        crawlres[i] = str;
      }
      ipos = str.indexOf("src='//");
      if (ipos>-1) {
        str = str.replace("src='//", "src='http://");
        crawlres[i] = str;
      }
      ipos = str.indexOf("src=\"//");
      if (ipos>-1) {
        str = str.replace("src=\"//", "src=\"http://");
        crawlres[i] = str;
      }      
      ipos = str.indexOf("src=/");
      if (ipos>-1) {
        str = str.replace("src=/", "src=http://"+targetDomain+"/");
        crawlres[i] = str;
      }
      ipos = str.indexOf("src='/");
      if (ipos>-1) {
        str = str.replace("src='/", "src='http://"+targetDomain+"/");
        crawlres[i] = str;
      }
      ipos = str.indexOf("src=\"/");
      if (ipos>-1) {
        str = str.replace("src=\"/", "src=\"http://"+targetDomain+"/");
        crawlres[i] = str;
      }      
      ipos = str.indexOf("src=http");
      if (ipos>-1) {
        continue;
      }
      ipos = str.indexOf("src='http");
      if (ipos>-1) {
        continue;
      }
      ipos = str.indexOf("src=\"http");
      if (ipos>-1) {
        continue;
      }
      str = str.replace("src=", "src=http://"+targetDomain+"/");
      crawlres[i] = str;
      str = str.replace("src='", "src='http://"+targetDomain+"/");
      crawlres[i] = str;
      str = str.replace("src=\"", "src=\"http://"+targetDomain+"/");
      crawlres[i] = str;
    }
     
     
    /* 
     * SEARCH FOR LOGO
     */
        var newcrawlres = [];
        var found = -1;
        for (i=0;i<crawlres.length;i++) {
          str = crawlres[i];                        
          if (str.indexOf("\"logo\"")>-1 || str.indexOf("'logo'")>-1 || str.indexOf("logo.png")>-1 || str.indexOf("logo.gif")>-1 || str.indexOf("logo.jpg")>-1 || str.indexOf("logo.jpeg")>-1) {
            found=i;
            //alert("found="+found);
            break;
          }
        }

        if (found>-1) {
          newcrawlres[0] = crawlres[found];
          //alert(newcrawlres[0]);
          var j=1;
          for (i=0;i<found;i++) {
            newcrawlres[j] = crawlres[i];              
            j++;
          }                
          for (i=found+1;i<crawlres.length;i++) {
            newcrawlres[j] = crawlres[i];              
            j++;
          }                
          crawlres = newcrawlres;
          logoFound = true;
          //alert(newcrawlres[0]);
        } 
    // END SEARCH 
    
    for (i=0;i<crawlres.length;i++) {
      document.write(crawlres[i]);
      if (verbose === 0) {
        document.write("<br>");
      }      
    }
    
  } else {

    document.write(JSON.stringify(crawlres));

  }

}

function postCrawling() {
	var colImages = document.images;
  var j = 0;
  var logoHeight = "300px";
	for(var i = 0; i < colImages.length; i++) {
    var oriWidth = colImages[i].width;
    var oriHeight = colImages[i].height;
    var d = oriWidth / oriHeight;
    if((parseInt(colImages[i].width) < 200) || (j>4) || colImages[i].style.visibility == "hidden" || colImages[i].style.display == "none") {
       colImages[i].style.display = "none";
       colImages[i].style.visibility = "hidden";
    } else {
       colImages[i].style.width = "100%"; 

       newWidth = colImages[i].width;
       newHeight = parseInt(newWidth / d); 

       colImages[i].style.maxWidth = "";
       colImages[i].style.float = "";
       colImages[i].style.height = "auto"; //newHeight + "px";
       colImages[i].style.position = "";
       colImages[i].style.top = "";
       colImages[i].style.left = "";
       colImages[i].style.margin = "";
       colImages[i].style.marginTop = "";
       colImages[i].style.marginBottom = "";
       colImages[i].style.marginLeft = "";
       colImages[i].style.marginRight = "";
       colImages[i].style.padding = "";
       colImages[i].style.paddingTop = "";
       colImages[i].style.paddingBottom = "";
       colImages[i].style.paddingLeft = "";
       colImages[i].style.paddingRight = "";
       colImages[i].style.border = "";

       //for transp pictures
       colImages[i].style.backgroundColor = "lightgray";

       if (i==0) {
         logoHeight = newHeight + "px";
       }
       j++;
    }
	}
  return j;
}

function writeFooter() {
  if (output == 1 && verbose == 1) {
    str = "<div style='text-align:center;font-size:9px'>Trademarks and brands are property of their respective owners.<br>.<br></div><div style='background-color:#03a9f5;height:105px;text-align:center;vetical-align:middle'><br><br><a href='/gr.php?to="+landingPage+"' target='_blank' style='text-decoration:none;font-weight:900;font-size:3.2vw;color:#FFFFFF;'>Go to "+landingPage+"</a></div>";
    document.write(str);
  }  
}

window.addEventListener("load", function() {
  if (!document.getElementById("grausi")) {
    document.write("<input id='grausi' type='hidden' value=''>");
  }  
  loadDoc();
}, true);  


function checkHttpResponse() {
  ret = false;
  str = document.getElementById("grausi").value;
  if (str.indexOf("<img")>-1) {
    ret = true;
  }
  return ret;
}

function launchParsing() {

  writeHeader();
  
  if (checkHttpResponse()) {
    
    crawlImages();
    setTimeout(nImgProcessed=postCrawling(), 1500);
    
    if (nImgProcessed==0 && recNum<3 && verbose!=0 && output!=2) {
      //document.body.innerHtml = "";
      loadDoc();
      return;
    }
    
  } else {
    if (recNum<3 && verbose!=0 && output!=2) {
      loadDoc();
    }
  }
  
  setTimeout(writeFooter(), 1700);

}



