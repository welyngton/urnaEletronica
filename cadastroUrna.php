<?php 
	include "dbConfig.php";

	$msg = "";   

	$title = "Urna";

	if(isset($_COOKIE['dadosUsr'])) {
		$dadosUsrSplit = explode(",",$_COOKIE['dadosUsr']);
		$loginUsr = $dadosUsrSplit[0];
		$tipoUsr = $dadosUsrSplit[2];		
	}	
	//Verifica se usuário tem permissão de acesso a tela
	if($tipoUsr != "admin") {	
		header("Location: http://localhost/UrnaIAC/login.php");
	}  

	//Marca se a eleição está ativa ou inativa
	$ativa = "Ativa";           
	
	$idEleicao = "";
	$tipoEleicao = "";
	$qtdDepEstadual = "0";
	$qtdDepFederal = "0";	
	$qtdVereador = "0";
	$inicio = "";			

	//verifica se existe uma eleição ativa
	$sqlSelectEleicaoAtiva = "SELECT * FROM eleicao WHERE ativa = 1";
	$resultSelectEleicaoAtiva = mysql_query($sqlSelectEleicaoAtiva);
	if ($resultSelectEleicaoAtiva === false) {
		echo "Could not successfully run query ($sqlSelectEleicaoAtiva) from DB: " . mysql_error();
		exit;
	}
	if (mysql_num_rows($resultSelectEleicaoAtiva) > 0) {  
		$row = mysql_fetch_row($resultSelectEleicaoAtiva);
		$idEleicao = $row[0];
		$tipoEleicao = $row[1];
		$qtdDepEstadual = $row[2];
		$qtdDepFederal = $row[3];		
		$qtdVereador = $row[4];
		$inicio = date("d/m/Y H:i",strtotime($row[5]));					
	} else {
		$ativa = "Inativa";
		$inicio = "";
		$tipoEleicao = "Municipal";
	}
	
	if($tipoEleicao == "Municipal") {
		$depEstMostrar = "none";
		$depFedMostrar = "none";
		$verMostrar = "table-row";
	}
	else if($tipoEleicao == "Federal") {
		$depEstMostrar = "table-row";
		$depFedMostrar = "table-row";
		$verMostrar = "none";
	} else {
		$depEstMostrar = "none";
		$depFedMostrar = "none";
		$verMostrar = "none";	
	}
	
	//Verifica o post
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		//Verifica se foi iniciada uma nova eleição
		if (isset($_POST['iniciarBtn'])) {
			//verificar se existe uma eleição ativa
			$sqlSelectEleicaoAtiva = "SELECT * FROM eleicao WHERE ativa = 1";
			$resultSelectEleicaoAtiva = mysql_query($sqlSelectEleicaoAtiva);
			if ($resultSelectEleicaoAtiva === false) {
				echo "Could not successfully run query ($sqlSelectEleicaoAtiva) from DB: " . mysql_error();
				exit;
			}
			if (mysql_num_rows($resultSelectEleicaoAtiva) > 0) {  
				$msg = "Já existe uma eleição ativa";
			} 
			//se não houver nenhuma eleição ativa, insere a nova eleição
			else {
				$ativa = "Ativa";
				$inicio = date("d/m/Y H:i",time()-18000);
				$tipoEleicao = $_POST["tipoEleicao"];
				$qtdDepEstadual = $_POST["qtdDepEstadual"];
				$qtdDepFederal = $_POST["qtdDepFederal"];		
				$qtdVereador = $_POST["qtdVereador"];
				//insere os dados da eleição no banco
				$sqlInsert = "INSERT INTO eleicao (tipoEleicao,qtdDepEstadual,qtdDepFederal,qtdVereador,inicio,ativa) VALUES ('$tipoEleicao',$qtdDepEstadual,$qtdDepFederal,$qtdVereador,now(),1)";
				$resultInsert = mysql_query($sqlInsert);
				if ($resultInsert === false) {
					echo "Could not successfully run query ($sqlInsert) from DB: " . mysql_error();
					exit;
				}
			}			
		} else if(isset($_POST['encerrarBtn'])) {
				//insere os dados do encerramento da eleicao no banco
				$sqlInsert = "update eleicao set ativa = 0, fim = now() where id = $idEleicao";
				$resultInsert = mysql_query($sqlInsert);
				if ($resultInsert === false) {
					echo "Could not successfully run query ($sqlInsert) from DB: " . mysql_error();
					exit;
				}
				$ativa = "Inativa";
				$inicio = "";
		}
	}

?>
	<?php include "header.php"; ?>
	
	<script type="text/javascript">
	   $(document).ready(function(){
			$('.selectpicker').selectpicker('val', '<?php echo $tipoEleicao; ?>');
	   });
	   $(document).ready(function() {
		  $('.selectpicker').on('change', function(){
			var selected = $(this).find("option:selected").val();
			if(selected == "Municipal") {
				$("#verMostrar").show();
				$("#depEstMostrar").hide();
				$("#depFedMostrar").hide();
			}
			else {
				if(selected == "Federal") {
					$("#verMostrar").hide();
					$("#depEstMostrar").show();
					$("#depFedMostrar").show();
				}
				else {			
					$("#verMostrar").hide();
					$("#depEstMostrar").hide();
					$("#depFedMostrar").hide();
				}
			}
		});
	});
	</script>	
	
	<ul class="nav nav-pills">
	  <li role="presentation" class="active"><a href="cadastroUrna.php" > Urna </a></li>
	  <li role="presentation"><a href="cadastroEleitor.php"> Cadastro Eleitor </a></li>
	  <li role="presentation"><a href="cadastroPartido.php"> Cadastro Partido </a></li>
	  <li role="presentation"><a href="cadastroCandidato.php"> Cadastro Candidato </a></li>
	  <li role="presentation"><a href="login.php" > Logout</a></li>		  
	</ul>

	<form class="form-urna" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
		<table class="table table-striped">
			<th> <h3> Cadastro Urna </h3> </th>
			<tr>
				<td style="color:red;">
				<?php echo $msg; ?></td>
			</tr> 
			<tr>
			<th><label for="name"><strong>Tipo Eleição:</strong></label></th>
			<td>
				<select id="tipoEleicao" name="tipoEleicao" class="selectpicker" >
				  <option value="Federal">Federal / Estadual</option>
  				  <option value="PresidenteGovernador2" >Presidente & Governador 2º Turno </option>
				  <option value="Presidente2" >Presidente 2º Turno</option>
				  <option value="Estadual2" >Governador 2º Turno </option>
				  <option value="Municipal" >Municipal</option>
				  <option value="Prefeito2" >Prefeito 2º Turno</option>				
				</select>
			</td>
			</tr>				
			<tr id="depEstMostrar" style="display:<?php echo $depEstMostrar; ?>;">
				<th><label for="qtdDepEstadual"><strong>Quantidade de Deputados Estaduais:</strong></label></th>
				<td><input class="form-control" name="qtdDepEstadual" id="qtdDepEstadual" type="text" size="30" value="<?php echo $qtdDepEstadual; ?>" /></td>
			</tr>		
			<tr id="depFedMostrar" style="display:<?php echo $depFedMostrar; ?>;">
				<th><label for="qtdDepFederal"><strong>Quantidade de Deputados Federais:</strong></label></th>
				<td><input class="form-control" name="qtdDepFederal" id="qtdDepFederal" type="text" size="30" value="<?php echo $qtdDepFederal; ?>" /></td>
			</tr>	
			<tr id="verMostrar" style="display:<?php echo $verMostrar; ?>;">
				<th><label for="qtdVereador"><strong>Quantidade de vereadores:</strong></label></th>
				<td><input class="form-control" name="qtdVereador" id="qtdVereador" type="text" size="30" value="<?php echo $qtdVereador; ?>" /></td>
			</tr>	
			<tr>
				<td>
				<input class="btn btn-lg btn-primary btn-block" type="submit" name="iniciarBtn" id="iniciarBtn" value="Iniciar Eleição" alt="Submit" title="Submit" />				
				</td>
				<td>
				<input class="btn btn-lg btn-primary btn-block" type="submit" name="encerrarBtn" id="encerrarBtn" value="Encerrar Eleição" alt="Submit" title="Submit" />
				</td>
			</tr>				
			<tr>
				<th><label for="name"><strong>Eleição iniciada em:</strong></label></th>
				<td><input class="form-control" name="inicioEleicao" id="inicioEleicao" type="text" size="30" value="<?php echo $inicio ?>" /></td>
				<td><label for="name"><?php echo $ativa; ?></label></td>
			</tr>	
			<tr>
				<td>
				<button class="btn btn-lg btn-primary btn-block" type="button"><a href="apuracao.php" role="button" style="color:white;">Apuração</a></button>				
				</td>
				<td>
				</td>
			</tr>								
		</table>
	</form>
<?php include "footer.php"; ?>