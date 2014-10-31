function preregLookup(name)
{	
	if (window.XMLHttpRequest)
   {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	xmlhttp.onreadystatechange=function()
	{
	  if (xmlhttp.readyState==4 && xmlhttp.status==200)
	 {
		document.getElementById("student_results").innerHTML=xmlhttp.responseText;
	  }
	};
	
	xmlhttp.open("GET","prereg_lookup.php?name="+name,true);
	xmlhttp.send();
}

function getStudentResult($name)
{
	if (window.XMLHttpRequest)
	   {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		}
		else
		{// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		xmlhttp.onreadystatechange=function()
		{
		  if (xmlhttp.readyState==4 && xmlhttp.status==200)
		 {
			document.getElementById("results").innerHTML=xmlhttp.responseText;
		  }
		};
		
		xmlhttp.open("GET","prereg_student_results.php?id="+name,true);
		xmlhttp.send();
}