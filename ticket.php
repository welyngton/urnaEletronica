<?php 

	include "dbConfig.php";
	
	$msgErro = "";
	$idEleicao;
	$title = "Ticket";   
	$logado = false;
	$nome = "";	
	$titulo = "";
	$secao = "";	
	$zona = "";	
	$data = "";	
	
	if(isset($_COOKIE['dadosUsr'])) {
		$dadosUsrSplit = explode(",",$_COOKIE['dadosUsr']);
		$loginUsr = $dadosUsrSplit[0];
		$idUsr = $dadosUsrSplit[1];		
		$tipoUsr = $dadosUsrSplit[2];		
	}	
	//Verifica se usuário tem permissão de acesso a tela
	if($tipoUsr != "eleitor") {	
		header("Location: http://localhost/UrnaIAC/login.php");
	}  

	//Verifica se usuário já votou nessa eleição
	//SELECT TICKET //
	$sqlEleicao = "SELECT id FROM eleicao WHERE ativa = 1";
	$resultEleicao = mysql_query($sqlEleicao);
	if ($resultEleicao === false) {
		echo "Erro na query ($sqlEleicao),do BD: " . mysql_error();
		exit;
	}	
	if (mysql_num_rows($resultEleicao) > 0) {
		$row = mysql_fetch_row($resultEleicao);
		$idEleicao = $row[0];
	}
	else {
		echo "Não existe eleição ativa.";
		exit;
	}
	$sqlTicket = "SELECT data FROM ticket WHERE idEleitor = $idUsr AND idEleicao = $idEleicao";
	$resultTicket = mysql_query($sqlTicket);
	if ($resultTicket === false) {
		echo "Erro na query ($sqlTicket), do BD: " . mysql_error();
		exit;
	}
	if (mysql_num_rows($resultTicket) > 0) {
		$row = mysql_fetch_row($resultTicket);
		$data = date("d/m/Y H:i",strtotime($row[0]));
	} else {
		$msgErroEleicao = "Você ainda não votou nessa eleição.";
	}
	//Dados eleitor
	$sqlIdUsr = "SELECT nome, titulo, zona, secao FROM eleitor WHERE idUsuario = $idUsr";
	$resultUsr = mysql_query($sqlIdUsr);
	if ($resultUsr === false) {
		echo "Erro na query ($sqlIdUsr), do BD: " . mysql_error();
		exit;
	}
	if (mysql_num_rows($resultUsr) > 0) { 
		$row = mysql_fetch_row($resultUsr);					
		$nome = $row[0];
		$titulo = $row[1];		
		$zona = $row[2];
		$secao = $row[3];					
	}
?>

<?php include "header.php"; ?>
	
	<ul class="nav nav-pills">
	  <li role="presentation" class="active"><a href="ticket.php" > Ticket </a></li>
	  <li role="presentation"><a href="login.php" > Logout</a></li>		  
	</ul>

	<form id="ticketForm" class="form-ticket" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
		<table class="table table-striped" >
			<th><h3> Ticket Eleitor </h3></th>
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msgErro; ?></td>
			</tr> 
			
			<tr>
				<th><label for="nome"><strong>Nome:</strong></label></th>
				<td><input class="form-control" name="nome" id="nome" type="text" size="30" value="<?php echo $nome ?>" disabled /></td>
			</tr>
			<tr>
				<th><label for="titulo"><strong>Título:</strong></label></th>
				<td><input class="form-control" name="titulo" id="titulo" type="text" size="30" value="<?php echo $titulo ?>" disabled /></td>
			</tr>
			<tr>
				<th><label for="zona"><strong>Número Zona Eleitoral:</strong></label></th>
				<td><input class="form-control" name="zona" id="zona" type="text" size="30" value="<?php echo $zona ?>" disabled /></td>
			</tr>
			<tr>
				<th><label for="secao"><strong>Número da seção:</strong></label></th>
				<td><input class="form-control" name="secao" id="secao" type="text" size="30" value="<?php echo $secao ?>" disabled /></td>
			</tr>
			<tr>
				<th><label for="data"><strong>Data:</strong></label></th>
				<td><input class="form-control" name="data" id="data" type="text" size="30" value="<?php echo $data ?>" disabled /></td>
			</tr>			
		</table>
	</form>		
	
<?php include "footer.php"; ?>