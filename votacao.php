<?php 
	include "dbConfig.php";

	//VARIÁVEIS DE MENSAGENS
	$msg = "";         
	$msgErro = "";
	$msgErroEleicao = "";	
	$msgVoto = ""; 	
	$avisoVoto = "";	
	//VARIÁVEIS DE INFO	
	$title = "Votação";
	$siglaPartido = "";	
	$nomeCandidato = "";
	$cargo = "";
	$urlFotoCandidato = "fotosCandidatos/blank.jpg";
	$idTicket = 0;
	//VARIÁVEIS DE CONTROLE		
	$votoNulo = false;	
	$votoLegenda = false;
	$votoBranco = false;
	$votoCandidato = false;
	$disable = false;	
	$fimVotacao = false;
	$maxDigitos = 5;
	//Arquivos
	$clickMP3="Click.mp3";
	//Design
	$displayRow = "block";

	//DADOS DA ELEICAO
	
	//-Vetor de Controle da Eleição-//
	#0 = numVereador, 1= numPrefeito, 2= numDepEstadual, 3= numDepFederal, 4= numSenador, 5= numGovernador, 6= numPresidente
	$ticketVoto = array(0 => null, 1 => null, 2 => null, 3 => null, 4 => null, 5 => null, 6 => null);		
	
	//Visor de digitos
	$digitosDisplay = array(0 => "", 1 => "", 2 => "", 3 => "", 4 => "");			
	//Contador de digitos da votação
	$contDigitos = 0;			

	//verifica se o usuário já logou e o tipo de usuário
	if(isset($_COOKIE['dadosUsr'])) {
		$dadosUsrSplit = explode(",",$_COOKIE['dadosUsr']);
		$loginUsr = $dadosUsrSplit[0];
		$idUsr = $dadosUsrSplit[1];		
		$tipoUsr = $dadosUsrSplit[2];		
	}	

	if(isset($_COOKIE['contDigitos'])) {
		$contDigitos = (int)$_COOKIE['contDigitos'];
	}			
		
	if(isset($_COOKIE['digitosDisplay'])) {
		$digitosParse = explode(",",$_COOKIE['digitosDisplay']);
		for($i = 0; $i < count($digitosParse); $i++)
			$digitosDisplay[$i] = $digitosParse[$i];
	}	
	
	if(isset($_COOKIE['ticketVoto'])) {
		$ticketParse = explode(",",$_COOKIE['ticketVoto']);
		for($i = 0; $i < count($ticketParse); $i++)
			$ticketVoto[$i] = $ticketParse[$i];
	}		
	
	if(isset($_COOKIE['contDigitos'])) {
		$contDigitos = (int)$_COOKIE['contDigitos'];
	}				
	
	function insereVoto($idTicket, $numCandidatoVoto, $cargo) {

		if(!empty($numCandidatoVoto)) {
			$numPartidoVoto = intval(substr($numCandidatoVoto,0,2));
		} else {
			$numCandidatoVoto = 0;
			$numPartidoVoto = 0;
		}
		$sqlInsertVoto = "INSERT INTO voto (idTicket, numeroCandidato, numeroPartido,cargo) VALUES ($idTicket,$numCandidatoVoto,$numPartidoVoto,'$cargo')";
		$resultInsertVoto = mysql_query($sqlInsertVoto);
		
		if ($resultInsertVoto === false) {
			echo "Erro na query ($sqlInsertVoto), do BD: " . mysql_error();
			exit;
		}		
	}
	
	//Verifica se usuário tem permissão de acesso a tela	
	if($tipoUsr != "eleitor") {	
		header("Location: http://localhost/UrnaIAC/login.php");
	}
	//Verifica se existe uma eleição ativa
	//SELECT ELEICAO //	
	$sqlEleicao = "SELECT id,tipoEleicao,fim FROM eleicao WHERE ativa = 1";	
	$resultSqlEleicao = mysql_query($sqlEleicao);
	if ($resultSqlEleicao === false) {
		echo "Erro na query ($sqlEleicao), do BD: " . mysql_error();
		exit;
	}
	if (mysql_num_rows($resultSqlEleicao) > 0) { 
		$row = mysql_fetch_row($resultSqlEleicao);
		$idEleicao = $row[0];	
		$tipoEleicao = $row[1];
		$dataFim = $row[2]; //Verificar se eleição já acabou
	
		//Verifica se usuário já votou nessa eleição
		//SELECT TICKET //
		$sqlTicket = "SELECT id, data FROM ticket WHERE idEleitor = $idUsr AND idEleicao = $idEleicao";
		$resultTicket = mysql_query($sqlTicket);
		if ($resultTicket === false) {
			echo "Erro na query ($sqlTicket), do BD: " . mysql_error();
			exit;
		}
		if (mysql_num_rows($resultTicket) > 0) {
			$row = mysql_fetch_row($resultTicket);
			if(!empty($row[1])) {
				$msgErroEleicao = "Seu voto já foi realizado nesta eleição.";
				$disable = true;
			}
			$idTicket = $row[0];
		}
		//Se usuário não votou registra ticket
		else {
			$sqlInsertTicket = "INSERT INTO ticket (idEleitor, idEleicao) VALUES ($idUsr, $idEleicao)";
			$resultInsertTicket = mysql_query($sqlInsertTicket);
			if ($resultInsertTicket === false) {
				echo "Erro na query ($sqlInsertTicket), do BD: " . mysql_error();
				exit;
			}
		}		
		//////////////////////
		//Controle eleição ///
		//////////////////////
		
		///**Municipal**///
		if($tipoEleicao == "Municipal") {
			if (empty($ticketVoto[0])) { 
				$cargo = "Vereador";
				$maxDigitos = 5;
			}
			if (!empty($ticketVoto[0]) && empty($ticketVoto[1])) {
				$cargo = "Prefeito";
				$maxDigitos = 2;
			}
		}
		///**Municipal**///	-> Segundo turno
		if($tipoEleicao == "Prefeito2") {
			if (empty($ticketVoto[1])) {
				$cargo = "Prefeito";
				$maxDigitos = 2;
			}
		}
		//Eleição Federal///
		if($tipoEleicao == "Federal") {
			if (empty($ticketVoto[2])) {
				$cargo = "Deputado Estadual";
				$maxDigitos = 5;
			}
			if (!empty($ticketVoto[2]) && empty($ticketVoto[3])) {
				$cargo = "Deputado Federal";
				$maxDigitos = 5;
			}
			if (!empty($ticketVoto[3]) && empty($ticketVoto[4])) {
				$cargo = "Senador";
				$maxDigitos = 3;
			}	
			if (!empty($ticketVoto[4]) && empty($ticketVoto[5])) {
				$cargo = "Governador";
				$maxDigitos = 2;
			}	
			if (!empty($ticketVoto[5]) && empty($ticketVoto[6])) {
				$cargo = "Presidente";
				$maxDigitos = 2;
			}				
		}			
		//Eleição Federal/// -> Segundo Turno Pres / Gov
		if($tipoEleicao == "PresidenteGovernador2") {
			if (empty($ticketVoto[5]) && empty($ticketVoto[6])) {
				$cargo = "Governador";
				$maxDigitos = 2;
			}	
			if (!empty($ticketVoto[5]) && empty($ticketVoto[6])) {
				$cargo = "Presidente";
				$maxDigitos = 2;
			}				
		}		
		//Eleição Federal/// -> Segundo Turno Presidente
		if($tipoEleicao == "Presidente2") {
			if (empty($ticketVoto[6])) {
				$cargo = "Presidente";
				$maxDigitos = 2;
			}	
		}	
		//Eleição Federal/// -> Segundo Turno Governador
		if($tipoEleicao == "Governador2") {
			if (empty($ticketVoto[5])) {
				$cargo = "Governador";
				$maxDigitos = 2;
			}	
		}	
			
		////////////////////////////////
		//INICIA VOTAÇÃO PARA ELEITOR //
		///////////////////////////////
		
		//Array de display da urna com os números digitados
		if($digitosDisplay == null) {
			$digitosDisplay = array(0 => "", 1 => "", 2 => "", 3 => "", 4 => "");
		}
		
		if($contDigitos == null) {
			$contDigitos = 0;
		}		

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {		

			//Verifica se o post veio das teclas
			if(!((isset($_POST['branco']))||(isset($_POST['confirma']))||(isset($_POST['corrige']))||(isset($_POST['key1']))||(isset($_POST['key2']))||(isset($_POST['key3']))||(isset($_POST['key4']))||(isset($_POST['key5']))||(isset($_POST['key6']))||(isset($_POST['key7']))||(isset($_POST['key8']))||(isset($_POST['key9']))||(isset($_POST['key0'])))) {
					$contDigitos = 0;			
					$digitosDisplay = array(0 => "", 1 => "", 2 => "", 3 => "", 4 => "");
				}
				else {

				}
			
			////////////////////////////////
			//VERIFICA REGRAS DO CONTADOR///
			////////////////////////////////

			if (isset($_POST['corrige'])) {
				$contDigitos = 0;
				$digitosDisplay = array(0 => "", 1 => "", 2 => "", 3 => "", 4 => "");	
			} else 	if(isset($_POST['branco'])) {
				$contDigitos = 0;
				$digitosDisplay = array(0 => "", 1 => "", 2 => "", 3 => "", 4 => "");
				$votoBranco = true;
				$msgErro = "";
				$msgVoto = "VOTO EM BRANCO";
				$avisoVoto = "<p class=\"labelAvisoVoto\"> Aperte a tecla: </p>
				<p class=\"avisoVoto\"> VERDE para CONFIRMAR este voto </p>
		    	<p class=\"avisoVoto\"> LARANJA para REINICIAR este voto </p>";
			//Confirmação de voto	
			} else if(isset($_POST['confirma'])) {
				if($votoBranco)
					$digitosDisplay = "0";
				//Gera ticket do voto
				if(($tipoEleicao == "Municipal")||($tipoEleicao == "Prefeito2")) {
					if($cargo == "Vereador") {
						$ticketVoto[0] = join($digitosDisplay);
						insereVoto($idTicket,$ticketVoto[0],$cargo);
					}
					if($cargo == "Prefeito") {
						$ticketVoto[1] = join($digitosDisplay);
						$fimVotacao = true;
						$displayRow = "none";
						insereVoto($idTicket,$ticketVoto[1],$cargo);
					}
				}
				if(($tipoEleicao == "Governador2")) {
					if($cargo == "Governador") {
						$ticketVoto[5] = join($digitosDisplay);
						$fimVotacao = true;
						$displayRow = "none";
						insereVoto($idTicket,$ticketVoto[5],$cargo);
					}		
				}
				if(($tipoEleicao == "Federal") || ($tipoEleicao == "Presidente2") || ($tipoEleicao == "PresidenteGovernador2")){
					if($cargo == "Deputado Estadual") {
						$ticketVoto[2] = join($digitosDisplay);
						insereVoto($idTicket,$ticketVoto[2],$cargo);
					}
					if($cargo == "Deputado Federal") {
						$ticketVoto[3] = join($digitosDisplay);
						insereVoto($idTicket,$ticketVoto[3],$cargo);
					}
					if($cargo == "Senador") {
						$ticketVoto[4] = join($digitosDisplay);
						insereVoto($idTicket,$ticketVoto[4],$cargo);
					}
					if($cargo == "Governador") {
						$ticketVoto[5] = join($digitosDisplay);
						insereVoto($idTicket,$ticketVoto[5],$cargo);
					}		
					if($cargo == "Presidente") {
						$ticketVoto[6] = join($digitosDisplay);
						$fimVotacao = true;
						$displayRow = "none";
						insereVoto($idTicket,$ticketVoto[6],$cargo);
					}							
				}

				setcookie("ticketVoto", join(",",$ticketVoto), time() + (3600), "/"); // 3600 = 1 hora	

				//Limpa campos para o próximo voto
				$cargo = "";
				$contDigitos = 0;
				$digitosDisplay = array(0 => "", 1 => "", 2 => "", 3 => "", 4 => "");	
				if($votoNulo) {
					$digitosDisplay = 0;					
				}
			}				
				
			//Libera ações dos dígitos somente se o array de dígitos ainda não estiver cheio
			if(($contDigitos <= 4) && ($contDigitos < $maxDigitos)){
				//Verifica se uma tecla da urna foi pressionada
				if (isset($_POST['key1'])) {
					$digitosDisplay[$contDigitos] = "1";
					$contDigitos++;
				}
				else
				if (isset($_POST['key2'])) {
					$digitosDisplay[$contDigitos] = "2";		
					$contDigitos++;		
				}
				else
				if (isset($_POST['key3'])) {
					$digitosDisplay[$contDigitos] = "3";				
					$contDigitos++;		
				}
				else
				if (isset($_POST['key4'])) {
					$digitosDisplay[$contDigitos] = "4";							
					$contDigitos++;		
				}
				else
				if (isset($_POST['key5'])) {
					$digitosDisplay[$contDigitos] = "5";							
					$contDigitos++;		
				}
				else
				if (isset($_POST['key6'])) {
					$digitosDisplay[$contDigitos] = "6";							
					$contDigitos++;		
				}
				else
				if (isset($_POST['key7'])) {
					$digitosDisplay[$contDigitos] = "7";							
					$contDigitos++;		
				}
				else
				if (isset($_POST['key8'])) {
					$digitosDisplay[$contDigitos] = "8";							
					$contDigitos++;		
				}
				else		
				if (isset($_POST['key9'])) {
					$digitosDisplay[$contDigitos] = "9";							
					$contDigitos++;		
				}
				else	
				if (isset($_POST['key0'])) {
					$digitosDisplay[$contDigitos] = "0";							
					$contDigitos++;		
				}				
			}
			//Verifica se já há mais de 2 dígitos no array de dígitos - voto liberado!
			if($contDigitos > 1) {
				//Altera mensagem de voto
					$avisoVoto = "<p class=\"labelAvisoVoto\"> Aperte a tecla: </p>
					  <p class=\"avisoVoto\"> VERDE para CONFIRMAR este voto </p>
					  <p class=\"avisoVoto\"> LARANJA para REINICIAR este voto </p>";
					///////////////////
					//SELECT PARTIDO //	
					$sqlSiglaPartido = "SELECT sigla FROM partido WHERE numero = ".$digitosDisplay[0].$digitosDisplay[1];
					$resultSiglaPartido = mysql_query($sqlSiglaPartido);
					if ($resultSiglaPartido === false) {
						echo "Erro na query ($sqlSiglaPartido), do BD: " . mysql_error();
						exit;
					}
					if (mysql_num_rows($resultSiglaPartido) > 0) {  
						$row = mysql_fetch_row($resultSiglaPartido);
						$siglaPartido = $row[0];
					}
					else {
						$msgErro = "NÚMERO ERRADO";
						$msgVoto = "VOTO NULO";
						$votoNulo = true;
					}
		
					///////////////////
					//SELECT CANDIDATO //	
					$sqlCandidatoVoto= "SELECT numero,nome,foto FROM candidato WHERE cargo='$cargo' and numero=".$digitosDisplay[0].$digitosDisplay[1].$digitosDisplay[2].$digitosDisplay[3].$digitosDisplay[4];
					$resultCandidatoVoto = mysql_query($sqlCandidatoVoto);
					if ($resultCandidatoVoto === false) {
						echo "Erro na query ($sqlCandidatoVoto), do BD: " . mysql_error();
						exit;
					}
					if(!$votoNulo) {
						if (mysql_num_rows($resultCandidatoVoto) > 0) {
							$row = mysql_fetch_row($resultCandidatoVoto);
							$numCandidato= $row[0];
							if($numCandidato==$digitosDisplay[0].$digitosDisplay[1].$digitosDisplay[2].$digitosDisplay[3].$digitosDisplay[4]) {
								$nomeCandidato = "Nome: ".$row[1];
								$urlFotoCandidato = $row[2];
								$votoCandidato = true;
							} else	{
							//CRIAR Condição a depender do candidato para exibir a msgErro conforme a qtd de
							//digitos do cargo
							$msgErro = "CANDIDATO INEXISTENTE";
							$msgVoto = "VOTO DE LEGENDA";
							$votoLegenda = true;
							}
						}
						else {
							$msgErro = "CANDIDATO INEXISTENTE";
							$msgVoto = "VOTO DE LEGENDA";
							$votoLegenda = true;
						}	
					}			  
			}
			//echo "Armazenando voto no Cookie:";
			setcookie("digitosDisplay", join(",",$digitosDisplay), time() + (3600), "/"); // 3600 = 1 hora							
			setcookie("contDigitos", $contDigitos, time() + (3600), "/"); // 3600 = 1 hora
			
			//Fim da votação
			//Voto armazenado
			if($fimVotacao) {
				// $sqlVoto = "";
				// for ($j = 0; $j < count($ticketVoto); $j++) {
					// if(empty($ticketVoto[$j])) {
						// $sqlVoto .= "null ,";
					// }
					// else
						// $sqlVoto .= $ticketVoto[$j]." ,";
				// }
				#$sqlUpdate = "INSERT INTO ticket (idEleitor, idEleicao, numVereador, numPrefeito, numDepEstadual, numDepFederal, numSenador, numGovernador, numPresidente, data) VALUES ($idUsr, $idEleicao, $sqlVoto now())";
				$sqlUpdate = "UPDATE ticket SET data = now() WHERE id = $idTicket";
				$resultInsert = mysql_query($sqlUpdate);
				if ($resultInsert === false) {
					echo "Erro na query ($sqlUpdate), do BD: " . mysql_error();
					exit;
				}
				$disable = true;				
			}
		}
	}	
	else {
		$msgErroEleicao="Não há eleição ativa no momento.";
		$disable = true;
	}
?>
	<?php include "header.php"; ?>
	
	<?php
	
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {		

		if((isset($_POST['branco']))||(isset($_POST['corrige']))||(isset($_POST['key1']))||(isset($_POST['key2']))||(isset($_POST['key3']))||(isset($_POST['key4']))||(isset($_POST['key5']))||(isset($_POST['key6']))||(isset($_POST['key7']))||(isset($_POST['key8']))||(isset($_POST['key9']))||(isset($_POST['key0']))) {
			echo"<audio id=\"clickAudio\"  preload=\"auto\" autoplay >
					<source src=\"Click.mp3\" type=\"audio/mp3\">
				</audio>\n";	
		}
		else{
			if(isset($_POST['confirma'])) {
				if(!$fimVotacao) {
					echo"<audio id=\"clickAudio\"  preload=\"auto\" autoplay >
						<source src=\"Confirma.mp3\" type=\"audio/mp3\">
					</audio>\n";	
				}
				else {
					echo"<audio id=\"clickAudio\"  preload=\"auto\" autoplay >
						<source src=\"UrnaFim.mp3\" type=\"audio/mp3\">
					</audio>\n";								
				}
			}			
		}
	}
	?>
	<ul class="nav nav-pills">
	  <li role="presentation" class="active"><a href="votacao.php" > Votação </a></li>
	  <li role="presentation"><a href="login.php" > Logout</a></li>		
    </ul>
	
	<form class="form-votacao" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
			<div style="color:red;">
				<?php echo $msgErroEleicao; ?></td>
			</div> 	
		<div class="row">
			<!-- PAINEL TELA DA URNA -->
			<div class="col-md-7" style="border: 5px solid #bbbbbb; padding-left:50px; min-height:410px; max-height: 410px;">		
				<?php 
					if($fimVotacao) {
						echo "<h1 class=\"fimVotacao\">FIM</h1>"; 
						echo "<a class=\"ticketLink\" href=\"ticket.php\">Imprimir Ticket</a>";
					}						
				?>
				  <!--Componentes tela da urna-->			  
						<?php if($cargo != "") echo "<p>Seu voto para </p>" ?>
					<!--Foto do candidato-->					
					<div class="row" style="clear: both;display: <?php echo $displayRow; ?>">
						<p style="margin-left:100px;"> <?php echo $cargo ?></p>
					</div>			
					<div class="row" style="display: <?php echo $displayRow; ?>">
					<a href="#" class="thumbnail" style="width:90px; height:120px; float:right; margin:10px;">
					<img src="<?php echo $urlFotoCandidato ?>" alt="...">
					</a>						
						<p style="display: <?php echo $displayRow; ?>"> Número:</p>				
						<div class="input-group">
						<span class="input-group-btn">
							<input id="digito1" name="digito1" class="btn btn-default btn-sm" type="button" value="<?php echo $digitosDisplay[0]; ?>" alt="" title="" disabled="disabled" />
							<input id="digito2" name="digito2" class="btn btn-default btn-sm" type="button" value="<?php echo $digitosDisplay[1]; ?>" alt="" title="" disabled="disabled" />
							<input id="digito3" name="digito3" class="btn btn-default btn-sm" type="button" value="<?php echo $digitosDisplay[2]; ?>" alt="" title="" disabled="disabled" />
							<input id="digito4" name="digito4" class="btn btn-default btn-sm" type="button" value="<?php echo $digitosDisplay[3]; ?>" alt="" title="" disabled="disabled" />
							<input id="digito5" name="digito5" class="btn btn-default btn-sm" type="button" value="<?php echo $digitosDisplay[4]; ?>" alt="" title="" disabled="disabled" />
						</span>
						</div>
						<h5>	<?php echo $msgErro; ?></h5>
					</div>
					<div class="row" style="display: <?php echo $displayRow; ?>">
						<h4 style="text-align:center;">	<?php echo $msgVoto; ?></h4>
					</div>							
					<div class="row" style="display: <?php echo $displayRow; ?>">
						<p>	<?php echo $nomeCandidato ?></p>
					</div>				
					<div class="row" style="display: <?php echo $displayRow; ?>">
						<label> Partido: </label>
						<input style="min-width:80px;" id="siglaPartido" name="siglaPartido" class="btn btn-default" type="button" value="<?php echo $siglaPartido; ?>" alt="" title="" disabled="disabled" />
					</div>	
					<div class="row" style="margin-top:5px; border-top:1px solid gray; display: <?php echo $displayRow; ?>">
						<?php echo $avisoVoto ?>
					</div>
				</div>
			<!-- PAINEL TECLADO DA URNA -->
		<div class="col-md-5" style="border: 5px solid #bbbbbb; padding-left:50px; padding-bottom:20px;min-height:410px;">
			<!--Lable teclado da urna-->
			<div class="row">
			<h3 style="margin-left:20px;"> UFPR - SEPT</h3>	 
			</div>
			<div class="row">
			<h4 style="margin-left:20px;"> TADS - IAC</h4>	 
			</div>			
		  <!--Teclado da urna-->
			<div class="row">
				<div class="col-md-8">
				<input id="key1" name="key1" class="btn btn-default btn-lg" type="submit" value="1" alt="1" title="1" <?php if($disable) echo "disabled"; ?>/>
				
				<input id="key2" name="key2" class="btn btn-default btn-lg" type="submit" value="2" alt="2" title="2" <?php if($disable) echo "disabled"; ?>/>
				
				<input id="key3" name="key3" class="btn btn-default btn-lg" type="submit" value="3" alt="3" title="3" <?php if($disable) echo "disabled"; ?>/>
				</div>
			</div>
			<div class="row">
				<div class="col-md-8">
				<input id="key4" name="key4" class="btn btn-default btn-lg" type="submit" value="4" alt="4" title="4" <?php if($disable) echo "disabled"; ?>/>
				
				<input id="key5" name="key5" class="btn btn-default btn-lg" type="submit" value="5" alt="5" title="5" <?php if($disable) echo "disabled"; ?>/>
				
				<input id="key6" name="key6" class="btn btn-default btn-lg" type="submit" value="6" alt="6" title="6" <?php if($disable) echo "disabled"; ?>/>
				</div>
			</div>
			<div class="row">
				<div class="col-md-8">
				<input id="key7" name="key7" class="btn btn-default btn-lg" type="submit" value="7" alt="7" title="7" <?php if($disable) echo "disabled"; ?>/>
				
				<input id="key8" name="key8" class="btn btn-default btn-lg" type="submit" value="8" alt="8" title="8" <?php if($disable) echo "disabled"; ?>/>
				
				<input id="key9" name="key9" class="btn btn-default btn-lg" type="submit" value="9" alt="9" title="9" <?php if($disable) echo "disabled"; ?>/>
				</div>
			</div>
			<div class="row">
				<div style="margin:5px 5px 5px 75px;" >
				<input id="key0" name="key0" class="btn btn-default btn-lg" type="submit" value="0" alt="0" title="0" <?php if($disable) echo "disabled"; ?>/>
				</div>
			</div>				
			<div class="row">
				<input id="branco" name="branco" class="btn btn-default btn-sm" type="submit" value="Branco" alt="Submit" title="Submit" <?php if($disable) echo "disabled"; ?> />
				
				<input id="corrige" name="corrige" class="btn btn-warning btn-sm" type="submit" value="Corrige" alt="Reset" title="Reset" <?php if($disable) echo "disabled"; ?>/>
				
				<input id="confirma" name="confirma" class="btn btn-success" type="Submit" value="Confirma" alt="Submit" title="Submit" <?php if($disable) echo "disabled"; ?>/>	
			</div>				
		</div>
		</div>
	</form>
</body>
</html>
