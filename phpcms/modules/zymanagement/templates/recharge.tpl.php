<?php 
    defined('IN_ADMIN') or exit('No permission resources.');
    include $this->admin_tpl('header','admin');
?>

<style>

.btn { display: inline-block; padding: 5px 12px !important; margin-bottom: 0; font-size: 12px; font-weight: 400; line-height: 1.32857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; margin-left: 5px;}
.btn-info { background-image: -webkit-linear-gradient(top,#5bc0de 0,#2aabd2 100%); background-image: -o-linear-gradient(top,#5bc0de 0,#2aabd2 100%); background-image: -webkit-gradient(linear,left top,left bottom,from(#5bc0de),to(#2aabd2)); background-image: linear-gradient(to bottom,#5bc0de 0,#2aabd2 100%); filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff5bc0de', endColorstr='#ff2aabd2', GradientType=0); filter: progid:DXImageTransform.Microsoft.gradient(enabled=false); background-repeat: repeat-x; border-color: #28a4c9;}
.btn-info { color: #fff; background-color: #5bc0de; border-color: #46b8da;}


</style>


<div class="subnav">
    <div class="content-menu ib-a blue line-x">
    <a class="add fb"><em>领导赠与</em></a>　
    </div>
	
</div>
<div class="content-menu  line-x">
<form name="textform" action="?m=zymanagement&c=goods&a=title_type" method="post" id="myform">
<tr>

	<div id='mnam1' style="display: <?php if ($info['gradeTitleType']==1) {?>none<?php }?>;">
   <input type="button" class="btn btn-info btn-sm addinput" value="增加" >
   <input type="button" class="btn btn-info btn-sm delinput" value="减少">
   <table style="width:20%;text-align: center">
	   
   <tr>
	   <br/><br/>
    <th></th>
    <th>类型</th>

   </tr>
  		 <tbody class="father">
     <input type="hidden" value="<?php echo $info["gradeNumber"]?>" name="gradeNumber" id="gradeNumber">
           <?php $num = 1;
               foreach($info as $key => $value){
           ?>
            <tr class="fixinput">
            <div>
    <!--$value['titleID']     <span style=" margin:0 10px;">--><?php //echo $value['tname']?><!--</span>-->

             <td><?php echo $num ?></td>
             <td><input type="text" name="titleID[<?php echo $key+1?>][title1]" required=""  value="<?php echo $value['title1']?>"></td>
                <td><input type="hidden" name="titleID[<?php echo $key+1?>][id]" required=""  value="<?php echo $value['id']?>"></td>
             
            </div>
            </tr>
             <?php $num++; } ?>
  		  </tbody>
    	</table>
		</div>

	<br/>
	 <input class="btn btn-info btn-sm" name="dosubmit" id="dosubmit" type="button" onclick="submit()" value="提交"/>
</tr>	
</form>
<br/>
<hr/>	
</div>
<div class="common-form">
 <?php   ?>
<form name="myform" action="?m=zymanagement&c=goods&a=boosGive" method="post" id="myform">
<table width="100%" class="table_form">

<tr>
<td  width="120"><?php echo L('类型：')?></td>

<td >
<select name='title'>
	<?php
               for($i=0;$i<count($infos);$i++){
           ?>
<option value="<?php echo $infos[$i]['title1'];?>"><?php echo $infos[$i]['title1'];?></option>
  <?php  } ?>
</select>

<tr>
<td  width="120"><?php echo L('用户名或手机号：')?></td>
<td><input type="text" name="username" size="15" value="" id="username"><span id="balance"><span></td>

 	
</tr>
<tr>
<td  width="120"><?php echo '数值：'?></td>
<td> <input type="text" name="amount" size="15" value="" id="unit"></td>
</tr>
<!--<tr>
<td  width="120"><?php echo '交易备注'?></td> 
<td><textarea name="usernote"  id="usernote" rows="5" cols="50"></textarea></td>
</tr>-->
</table>
<div class="bk15"></div>
<div class="subnav">
    <div class="content-menu ib-a blue line-x">
        <input name="dosubmit" type="submit" value="<?php echo L('submit')?>" class="btn btn-info btn-sm addinput" id="dosubmit">
    </div>
</div>

</form>
	


		
</div>
</body>
</html>
<script type="text/javascript">
	$('.addinput').on('click',function(){
		var index = $(".fixinput").size()+1;
		if(index<=10){
		var imgshow =' <tr class="fixinput"> <div class="fjpz" style=" width: 50%;" > <td>'+index+'</td> <td><input type="text" name="titleID['+index+'][TitleContent]" class="input-text" required=""  value="样式'+index+'"></td> </div> </tr>';
		$('.father').append(imgshow);
		}else{
			alert("样式不能超过十");
		}
		$("#gradeNumber").val(index);
	});
	$('.delinput').on('click',function(){
        //var index = $(".father").size();
        $('.fixinput').last().remove();
        $("#gradeNumber").val($(".fixinput").size());
    });

    
</script>


