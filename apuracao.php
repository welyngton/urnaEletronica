<?php 
	include "dbConfig.php";
	
	$msg = "";  
	$title = "Apuração";
	//Variáveis da eleicao
	$qtdVereador = 0;
	$qtdDepEstadual = 0;
	$qtdDepFederal = 0;
	$qtdVagas = 0;
	$qtdEleito = 0;
	$idEleicao = null;
	$tipoEleicao = "";
	$cargoApuracao = "";
	$zonaApuracao = 0;
	$secaoApuracao = 0;
	//$apuracaoEleicao = $array();
	$apuracaoCargo = array();
	
	if(isset($_COOKIE['dadosUsr'])) {
		$dadosUsrSplit = explode(",",$_COOKIE['dadosUsr']);
		$loginUsr = $dadosUsrSplit[0];
		$idUsr = $dadosUsrSplit[1];		
		$tipoUsr = $dadosUsrSplit[2];		
	}	
	//Verifica se usuário tem permissão de acesso a tela
	if($tipoUsr != "admin") {	
		header("Location: http://localhost/UrnaIAC/login.php");
	}  
		
//	if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$sqlEleicao = "SELECT id, tipoEleicao, qtdVereador, qtdDepEstadual, qtdDepFederal FROM eleicao order by id desc limit 1";
	$resultEleicao = mysql_query($sqlEleicao);
	if ($resultEleicao === false) {
		echo "Erro na query ($sqlEleicao), do BD: " . mysql_error();
		exit;
	}	
	if (mysql_num_rows($resultEleicao) > 0) {
		$row = mysql_fetch_row($resultEleicao);
		$idEleicao = $row[0];
		$tipoEleicao = $row[1];
		$qtdVereador = $row[2];
		$qtdDepEstadual = $row[3];
		$qtdDepFederal = $row[4];
	}
	else {
		echo "Não existe eleição ativa.";
		exit;
	}
	if($tipoEleicao == "Municipal"){
		
	}
	else if($tipoEleicao == "Prefeito2") {
		
	}
	else if($tipoEleicao == "Federal") {
		
	}
	else if($tipoEleicao == "Governador2") {
		
	}
	else if($tipoEleicao == "Presidente2") {
		
	}	
	else if($tipoEleicao == "PresidenteGovernador2"){
		
	}
	
	$sqlTicket = "SELECT * FROM ticket WHERE idEleitor = $idUsr AND idEleicao = $idEleicao";
	$resultTicket = mysql_query($sqlTicket);
	if ($resultTicket === false) {
		echo "Erro na query ($sqlTicket), do BD: " . mysql_error();
		exit;
	}
	if (mysql_num_rows($resultTicket) > 0) {
		$row = mysql_fetch_row($resultTicket);
		$data = date("d/m/Y H:i",strtotime($row[0]));
	} else {
		$msgErroEleicao = "Ainda não há votos nessa eleição.";
	}
		
	?>

	<?php include "header.php"; ?>

	<ul class="nav nav-pills">
	  <li role="presentation" class="active"><a href="apuracao.php" > apuracao </a></li>
	  <li role="presentation"><a href="cadastroUrna.php"> Cadastro Urna </a></li>	  
	  <li role="presentation"><a href="login.php" > Logout</a></li>		  
	</ul>
	
	<form class="form-apuracao" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
		<table class="table table-striped">
		<th><h3> Apuração</h3></th>
		<tr>
			<th><label for="zonaApuracao"><strong>Número da zona:</strong></label></th>
			<td><input class="form-control" name="zonaApuracao" id="zonaApuracao" type="text" size="30" /></td>
		</tr>			
		<tr>
			<th><label for="secaoApuracao"><strong>Número da sessão:</strong></label></th>
			<td><input class="form-control" name="secaoApuracao" id="secaoApuracao" type="text" size="30" /></td>
		</tr>		
		<tr>
			<th><label for="cargoApuracao"><strong>Cargo:</strong></label></th>
			<td><select id="cargoApuracao" name="cargoApuracao" class="selectpicker" >
			  <option value="0">Selecione...</option>	
			  <option value="Vereador">Vereador</option>	
			  <option value="Prefeito">Prefeito</option>				
			  <option value="Deputado Estadual">Deputado Estadual</option>
			  <option value="Deputado Federal">Deputado Federal</option>
			  <option value="Senador">Senador</option>
			  <option value="Governador">Governador</option>
			  <option value="Presidente">Presidente</option>				  
			</select></td>
		</tr>
		<tr>		
			<td></td>
				<td>
				<input class="btn btn-lg btn-primary btn-block" type="submit" id="apurarEleicao" name="apurarEleicao" value="Apurar" alt="Apurar" title="Apurar" />				
				</td>
			</tr>
		</table>
	
		<table class="table table-striped">
			<th>Votos</th>
			<th>Nome</th>
			<th>Número</th>
			<th>Partido</th>
			<th>Coligação</th>
			<th>Resultado</th>
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr>	
			<?php
			if(isset($_POST['apurarEleicao'])) {
				
				if(empty($_POST["zonaApuracao"])) {
					$zonaApuracao = 0;
				}
				else
					$zonaApuracao = $_POST["zonaApuracao"];
				
				if(empty($_POST["secaoApuracao"])) {
					$secaoApuracao = 0;
				}
				else
					$secaoApuracao = $_POST["secaoApuracao"];
				
				$cargoApuracao = $_POST["cargoApuracao"];
				
				if($cargoApuracao == "0")
					exit;
				
				switch($cargoApuracao) {
					case 'Vereador':
						$qtdVagas = $qtdVereador;
					break;
					case 'Prefeito':
						$qtdVagas = 1;
					break;
					case 'DepEstadual':
						$qtdVagas = $qtdDepEstadual;
					break;
					case 'DepFederal':
						$qtdVagas = $qtdDepFederal;
					break;
					case 'Senador':
						$qtdVagas = 1;
					break;
					case 'Governador':
						$qtdVagas = 1;
					break;
					case 'Presidente':
						$qtdVagas = 1;
					break;					
				}
				$sqlWhere = "";
				//Busca por parâmetros
				if(!empty($zonaApuracao)){ 
					$sqlWhere = " WHERE u.zona = '$zonaApuracao' ";
				}
				if(!empty($secaoApuracao)){ 
					if(empty($sqlWhere)) 
						$sqlWhere = "WHERE u.secao = '$secaoApuracao' ";
					else
						$sqlWhere .= "AND u.secao = '$secaoApuracao' ";
				}
				if(!empty($cargoApuracao)){ 
					if(empty($sqlWhere)) 
						$sqlWhere = "WHERE v.cargo = '$cargoApuracao' ";
					else
						$sqlWhere .= "AND v.cargo = '$cargoApuracao' ";
				}							
				$sqlApuracao = "call sp_apuracao($idEleicao,'$cargoApuracao',$zonaApuracao,$secaoApuracao)";

				$resultApuracao = mysql_query($sqlApuracao);
				if ($resultApuracao === false) {
					echo "Erro na query ($sqlApuracao), do BD: " . mysql_error();
					exit;
				}
				else  {      
					while ($row = mysql_fetch_array($resultApuracao, MYSQL_NUM)) {
						$votosCargo = $row;
						array_push($apuracaoCargo, $votosCargo);
					}
					//Quantidade de votos
					$len = count($apuracaoCargo);
					//Verifica se a eleição é para cargo de legenda
					if(($cargoApuracao == 'Vereador')||($cargoApuracao == 'DepEstadual')||($cargoApuracao == 'DepFederal')) {
						$posCandLeg = 1;					
						$partido = $apuracaoCargo[0][5];
						for($i = 0; $i < $len; $i++) {
							if($partido != $apuracaoCargo[$i][5]) {
								$partido = $apuracaoCargo[$i][5];
								$posCandLeg = 1;
							}
							$apuracaoCargo[$i][3] = $posCandLeg++;
							if($apuracaoCargo[$i][3] <= $apuracaoCargo[$i][2]) {
								$apuracaoCargo[$i][7] = "Eleito";
								$qtdEleito++;
							}
						}	
						$cadeirasLegenda = 0;
						$legenda = $apuracaoCargo[0][10];
						for($i = 0; $i < $len; $i++) {
							if($legenda != $apuracaoCargo[$i][10]) {
								$legenda = $apuracaoCargo[$i][10];
								$cadeirasLegenda = 0;
							}
							if($apuracaoCargo[$i][7] == "Eleito") {
								$cadeirasLegenda++;
								for($j = 0; $j < $len; $j++) {
									if($legenda == $apuracaoCargo[$j][10])
										$apuracaoCargo[$j][8] = $cadeirasLegenda;
								}
							}
						}
						//Verifica as sobras
						while($qtdEleito < $qtdVagas) {
							for($i = 0; $i < $len; $i++) {
								//Verifica se a legenda atingiu o quociente
								if($apuracaoCargo[$i][2] >= 1) {
									$apuracaoCargo[$i][9] = $apuracaoCargo[$i][2] / (intval($apuracaoCargo[$i][8]) + 1);
								}
								else {
									$apuracaoCargo[$i][9] = 0;
								}
							}
							$maiorMedia = 0;
							$legendaMaiorMedia = null;
							$indiceMaiorMedia = null;
							for($i = 0; $i < $len; $i++) {
								if($apuracaoCargo[$i][9] > $maiorMedia) {
									$legendaMaiorMedia = $apuracaoCargo[$i][10];
									$maiorMedia = $apuracaoCargo[$i][9];
								}
							}	
							//Distribui cadeira ao candidato mais votado dentro da legenda ganhadora da cadeira
							for($i = 0; $i < $len; $i++) {
								if($apuracaoCargo[$i][10] == $legendaMaiorMedia) 
									if($apuracaoCargo[$i][7] == "Não Eleito") {
										$apuracaoCargo[$i][7] = "Eleito";
										$qtdEleito++;
										break;
									}	
							}						
						}
					}
					else {
						$indxMaixVotado = 0;
						for($i = 0; $i < $len; $i++) {
							if(intval($apuracaoCargo[$i][1]) > intval($apuracaoCargo[$indxMaixVotado][1]))
								$indxMaixVotado = $i;
						}	
						$apuracaoCargo[$indxMaixVotado][7] = "Eleito";
					}
					
					for($i = 0; $i < $len; $i++) {
						echo "
						<tr>
						<td>".$apuracaoCargo[$i][1]."</td>
						<td>".$apuracaoCargo[$i][13]."</td>
						<td>".$apuracaoCargo[$i][4]."</td>
						<td>".$apuracaoCargo[$i][14]."</td>
						<td>".$apuracaoCargo[$i][15]."</td>
						<td>".$apuracaoCargo[$i][7]."</td>
						</tr>";
					}
				}
			}
			?>
		</table>
	</form>	
</body>
</html>