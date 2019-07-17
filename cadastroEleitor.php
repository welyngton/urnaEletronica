<?php 

	include "dbConfig.php";
	include_once ('phpQuery-onefile.php');

	$msgErro = "";
	$title = "Eleitor";   
	$logado = false;
	$nome = "";	
	$login = "";	
	$senha = "";	
	$nascimento = "";	
	$cep = "";	
	$cepValido = false;
	$titulo = "";	
	$zona = "";	
	$secao = "";	
	$idUsr = "";
	$loginUsr = "";
	$tipoUsr = "";	
	$dadosCEP = array ("","");
	
	//'none' para não mostrar ou 'block' para mostrar
	$mostrarVoltar = "none";
	$mostrarTabela = "none";

	function simple_curl($url,$post=array(),$get=array()){
		$url = explode('?',$url,2);
		if(count($url)===2){
			$temp_get = array();
			parse_str($url[1],$temp_get);
			$get = array_merge($get,$temp_get);
		}

		$ch = curl_init($url[0]."?".http_build_query($get));
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		return curl_exec ($ch);
	}	

	
	function getCorreiosCEP($cep) {
		
		$html = simple_curl('http://m.correios.com.br/movel/buscaCepConfirma.do',array(
		'cepEntrada'=>$cep,
		'tipoCep'=>'',
		'cepTemp'=>'',
		'metodo'=>'buscarCep'
		));

		phpQuery::newDocumentHTML($html, $charset = 'utf-8');

		$dados = 
		array(
			'logradouro'=> trim(pq('.caixacampobranco .resposta:contains("Logradouro: ") + .respostadestaque:eq(0)')->html()),
			'bairro'=> trim(pq('.caixacampobranco .resposta:contains("Bairro: ") + .respostadestaque:eq(0)')->html()),
			'cidade/uf'=> trim(pq('.caixacampobranco .resposta:contains("Localidade / UF: ") + .respostadestaque:eq(0)')->html()),
			'cep'=> trim(pq('.caixacampobranco .resposta:contains("CEP: ") + .respostadestaque:eq(0)')->html())
		);

		$dados['cidade/uf'] = explode('/',$dados['cidade/uf']);
		$dados['cidade'] = trim($dados['cidade/uf'][0]);
		//unset($dados['cidade/uf']);

		if(isset($dados['logradouro']) && (count($dados['logradouro']) > 0)) {
			if(isset($dados['logradouro']) && ($dados['logradouro'] != null)) {
				$dados['uf'] = trim($dados['cidade/uf'][1]);
				$cepValido = true;
			}
		}
		//die(json_encode($dados));		
		return $dados;
	}	

	//Verifica se usuário está logado
	if(isset($_COOKIE['dadosUsr'])) {
		$dadosUsrSplit = explode(",",$_COOKIE['dadosUsr']);
		$loginUsr = $dadosUsrSplit[0];
		$idUsr = $dadosUsrSplit[1];
		$tipoUsr = $dadosUsrSplit[2];

		$sqlIdUsr = "SELECT e.nome, u.login, e.nascimento, e.cep, e.zona, e.secao, e.titulo, e.idUsuario, u.id  FROM usuario u inner join eleitor e on u.id = e.idUsuario AND u.id = $idUsr";
		$resultUsr = mysql_query($sqlIdUsr);
		if ($resultUsr === false) {
			echo "Erro na query ($sqlIdUsr) do BD: " . mysql_error();
			exit;
		}
		if (mysql_num_rows($resultUsr) > 0) { 
			$row = mysql_fetch_row($resultUsr);					
			$nome = $row[0];	
			$login = $row[1];		
			$nascimento = $row[2];
			$cep = $row[3];
			$zona = $row[4];
			$secao = $row[5];							
			$titulo = $row[6];	
			$dadosCEP = getCorreiosCEP($cep);			
		}					
	}	

	//altera variáveis de controle de permissão	
	if ($loginUsr != null && ($tipoUsr != 'admin')) {
		$logado = true;
	} else {
		if ($tipoUsr == 'admin') {
			$mostrarVoltar = "block";
			$mostrarTabela = "block";
		}
	}
	
	//Verifica se foi chamado um GET
	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		if (isset($_GET['action'])) {
			if($_GET["action"] == "editar") {
				if (isset($_GET['id'])) {
					$idEditar = $_GET['id'];
					$sqlEditarUsr = "SELECT e.nome, u.login, e.nascimento, e.cep, e.zona, e.secao, e.titulo, e.idUsuario, u.id  FROM usuario u inner join eleitor e on u.id = e.idUsuario where u.id = $idEditar";
					$resultEditarUsr = mysql_query($sqlEditarUsr);
					if ($resultEditarUsr === false) {
						echo "Erro na query: ($sqlEditarUsr), do BD: " . mysql_error();
						exit;
					}
					if (mysql_num_rows($resultEditarUsr) > 0) { 
						$row = mysql_fetch_row($resultEditarUsr);					
						$nome = $row[0];	
						$login = $row[1];		
						$nascimento = $row[2];
						$cep = $row[3];
						$zona = $row[4];
						$secao = $row[5];							
						$titulo = $row[6];	
						//obtem cep via webservice dos correios
						$dadosCEP = getCorreiosCEP($cep);		
					}
				}
			}
			if($_GET["action"] == "excluir") {
				if (isset($_GET['id'])) {
					$idExcluir = $_GET['id'];
					$sqlExcluirUsr = "DELETE FROM usuario WHERE id = $idExcluir";
					$resultExcluirUsr = mysql_query($sqlExcluirUsr);
					$sqlExcluirEleitor = "DELETE FROM eleitor WHERE idUsuario = $idExcluir";
					$resultExcluirEleitor = mysql_query($sqlExcluirEleitor);
					if ($resultExcluirUsr === false){
						echo "Erro na query: ($sqlExcluirUsr), do BD: " . mysql_error();
						exit;
					}
					if ($resultExcluirEleitor === false){
						echo "Erro na query: ($sqlExcluirEleitor), do BD: " . mysql_error();
						exit;
					}			
				}	
			}
		}
	}		

	//Verifica se foi chamado um POST
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		if (isset($_POST['salvarUsuario'])) {
			if(($_POST["senha"] != $_POST["senha2"]) || (strlen($_POST["senha"]) < 4)) {
				$msgErro = "A senha e a confirmação devem ser iguais! As senhas devem ser ter mais de 4 dígitos.";
			}
			else {
				$nome = $_POST["nome"];
				$login = $_POST["login"];			
				$senha = md5($_POST["senha"]);
				$nascimento = $_POST["nascimento"];
				$cep = $_POST["cep"];
				$titulo = $_POST["titulo"];
				$zona = $_POST["zona"];
				$secao = $_POST["secao"];
				//obtem cep via webservice dos correios
				$dadosCEP = getCorreiosCEP($cep);		
				//verificar se login já existe		
				$sqlSelectLogin = "SELECT login FROM usuario WHERE login = '$login'";
				$resultSelectLogin = mysql_query($sqlSelectLogin);
				if ($resultSelectLogin === false) {
					echo "Erro na query ($sqlSelectLogin), do BD: " . mysql_error();
					exit;
				}
				//Se o login já existir, atualiza o usuário
				if (mysql_num_rows($resultSelectLogin) > 0) {  
					//Verificar necessidade de validar outros campos
					if(!empty($dadosCEP['logradouro'])) {
						$sqlUpdateUsr = "UPDATE usuario SET senha = '$senha' WHERE login = '$login'";
						$sqlUpdateEleitor = "UPDATE eleitor SET nome = '$nome', nascimento ='$nascimento', cep = '$cep', zona = '$zona', secao = '$secao', titulo = '$titulo' WHERE idUsuario = '$idUsr'";
						$resultUpdateUsr = mysql_query($sqlUpdateUsr);
						$resultUpdateEleitor = mysql_query($sqlUpdateEleitor);
						if(($resultUpdateUsr == false) || ($resultUpdateEleitor == false)) {
							echo "Erro na query  Update usuario do BD: " . mysql_error();
							exit;
						}
					}
					else {
						$msgErro = "CEP inválido.";
					}
				}
				//Caso login não exista continua com a inserção do usuário
				else {		
					//insere os dados do usuario no sistema
					$sqlInsert = "INSERT INTO usuario (login,senha,tipo) VALUES ('$login', '$senha','eleitor')";
					$resultInsert = mysql_query($sqlInsert);
					if ($resultInsert === false) {
						echo "Erro na query ($sqlInsert), do BD: " . mysql_error();
						exit;
					}				
					//obtem o login do usuario cadastrado no sistema
					$sqlSelect = "SELECT * FROM usuario WHERE login = '$login'";
					$resultSelect = mysql_query($sqlSelect);
					if ($resultSelect === false) {
						echo "Erro na query ($sqlSelect), do BD: " . mysql_error();
						exit;
					}

					//insere os dados do eleitor
					if (mysql_num_rows($resultSelect) > 0) {  
						$row = mysql_fetch_row($resultSelect);
						$idUsuario = $row[0];
						$tipoUsuario = $row[3];
						$sqlInsert2 = "INSERT INTO eleitor (nome,nascimento,cep,zona,secao,idUsuario,titulo) VALUES ('$nome', '$nascimento','$cep','$zona','$secao','$idUsuario','$titulo')";
						$resultSelect2 = mysql_query($sqlInsert2);
						if ($resultSelect2 === false) {
							echo "Erro na query ($sqlInsert2), do BD: " . mysql_error();
							exit;
						}
						//("Nome,ID,TipoUsr")
						//armazena o login em cookie para liberar o botão de voto e acesso a página de votação
						//Caso o usuário não seja administrador
						if($tipoUsr != "admin")
							setcookie("dadosUsr", $login.",".$idUsuario.",".$tipoUsuario, time() + (3600), "/"); // 3600 = 1 hora			
					}	
				}
			}
		}		
	}
?>

<?php include "header.php"; ?>
	
	<ul class="nav nav-pills">
	  <li role="presentation" class="active"><a href="cadastroEleitor.php" > Eleitor </a></li>
	  <?php
	  //Esconde ou mostra os menus do administrador
	  if($tipoUsr == "admin") {
		echo "
		<li role=\"presentation\" ><a href=\"cadastroUrna.php\"> Cadastro Urna </a></li>
		"; 
	  }?>
	  <li role="presentation"><a href="login.php" > Logout</a></li>		  
	</ul>

	<form id="cadastroEleitorForm" class="form-eleitor-cadastro" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" >
		<table class="table table-striped" >
			<th><h3> Cadastro Eleitor </h3></th>
			<tr>
			<td></td>
				<td style="color:red;">
				<?php echo $msgErro; ?></td>
			</tr> 
			
			<tr>
				<th><label for="name"><strong>Nome:</strong></label></th>
				<td><input class="form-control" name="nome" id="nome" type="text" size="30" value="<?php echo $nome ?>" required /></td>
			</tr>
			<tr>
				<th><label for="login"><strong>Login:</strong></label></th>
				<td><input class="form-control" name="login" id="login" type="text" size="30" value="<?php echo $login ?>" required /></td>
			</tr>			
			<tr>
				<th><label for="senha"><strong>Senha:</strong></label></th>
				<td><input class="form-control"name="senha" id="senha" type="password" size="30" required /></td>			
			</tr>
			<tr>
				<th><label for="senha2"><strong>Confirmação Senha:</strong></label></th>
				<td><input class="form-control"name="senha2" id="senha2" type="password" size="30" required /></td>		
			</tr>
			<tr>
				<th><label for="nascimento"><strong>Data Nascimento:</strong></label></th>
				<td><input class="form-control"name="nascimento" id="nascimento" type="text" size="30" value="<?php echo $nascimento ?>" required /></td>
			</tr>
			<tr>
				<th><label for="cep"><strong>CEP:</strong></label></th>
				<td><input class="form-control" name="cep" id="cep" type="text" size="30" value="<?php echo $cep ?>" required /></td>
			</tr>
			<tr>
				<th><label for="cep"><strong>Rua:</strong></label></th>
				<td><input class="form-control" name="rua" id="rua" type="text" size="30" value="<?php if(!empty($dadosCEP['logradouro'])) echo $dadosCEP['logradouro'] ?>" disabled /></td>
			</tr>
			<tr>
				<th><label for="cep"><strong>Bairro:</strong></label></th>
				<td><input class="form-control" name="bairro" id="bairro" type="text" value="<?php if(!empty($dadosCEP['bairro'])) echo $dadosCEP['bairro'] ?>" size="30" disabled /></td>
			</tr>
			<tr>
				<th><label for="cep"><strong>Cidade:</strong></label></th>
				<td><input class="form-control" name="cidade" id="cidade" type="text" value="<?php if(!empty($dadosCEP['cidade'])) echo $dadosCEP['cidade'] ?>" size="30" disabled /></td>
			</tr>			
			<tr>
				<th><label for="titulo"><strong>Título:</strong></label></th>
				<td><input class="form-control" name="titulo" id="titulo" type="text" value="<?php echo $titulo ?>" size="30" required /></td>
			</tr>			
			<tr>
				<th><label for="zona"><strong>Número Zona Eleitoral:</strong></label></th>
				<td><input class="form-control" name="zona" id="zona" type="text" size="30" value="<?php echo $zona ?>" required /></td>
			</tr>
			<tr>
				<th><label for="secao"><strong>Número da seção:</strong></label></th>
				<td><input class="form-control" name="secao" id="secao" type="text" size="30" value="<?php echo $secao ?>" required /></td>
			</tr>
			<tr>
			<td>
				<button class="btn btn-lg btn-primary btn-block" id="limparEleitor" name="limparEleitor" type="reset">Limpar</button>			
			</td>
			<td>
				<button class="btn btn-lg btn-primary btn-block" type="submit" id="salvarUsuario" name="salvarUsuario" >Salvar</button>			
			</td>
			</tr>
		</table>
	</form>	

	  <?php
	  //Mostra o botão de votação caso o usuário seja eleitor
	  if($tipoUsr == "eleitor") {
		echo "	
		<a class=\"btn btn-lg btn-success\" style=\"margin: 0px 0px 0px 30px;color:white;\" href=\"votacao.php\">VOTAR</a>
	  ";} ?>
	  
	<form class="form-eleitor-pesquisa" action="<?= $_SERVER['PHP_SELF'] ?>" method="post" style="display:<?php echo $mostrarTabela?>;" >
		<table class="table table-striped" >	
			<th>
			<h2>Pesquisa de eleitor</h2>
			</th>
			<tr>
				<th><label for="nomePesquisa"><strong>Nome:</strong></label></th>
				<td><input class="form-control" name="nomePesquisa" id="nomePesquisa" type="text" size="30" value="<?php $usr ?>"  /></td>
			</tr>
			<tr>
				<th><label for="loginPesquisa"><strong>Login:</strong></label></th>
				<td><input class="form-control" name="loginPesquisa" id="loginPesquisa" type="text" size="30" /></td>
			</tr>		
			<tr>
				<th><label for="tituloPesquisa"><strong>Título:</strong></label></th>
				<td><input class="form-control" name="tituloPesquisa" id="tituloPesquisa" type="text" size="30" /></td>
			</tr>	
			<tr>
				<th><label for="secaoPesquisa"><strong>Seção:</strong></label></th>
				<td><input class="form-control" name="secaoPesquisa" id="secaoPesquisa" type="text" size="30" /></td>
			</tr>	
			<tr>
				<th><label for="zonaPesquisa"><strong>Zona:</strong></label></th>
				<td><input class="form-control" name="zonaPesquisa" id="zonaPesquisa" type="text" size="30" /></td>
			</tr>				
				<td>
				</td>
				<td>
				<button class="btn btn-lg btn-primary btn-block" id="pesquisarEleitor" name="pesquisarEleitor" type="submit">Pesquisar</button>			
				</td>
			</tr>			
		</table>

	<?php 
		if($tipoUsr == "admin") {
			echo "<table class=\"table table-striped\" >
			<th>Nome</th>
			<th>Login</th>
			<th>Nascimento</th>
			<th>CEP</th>
			<th>Zona</th>
			<th>Seção</th>
			<th>Título</th>
			<th>Ações</th>";
			if(isset($_POST['pesquisarEleitor'])) {
				$nomePesquisa = $_POST["nomePesquisa"];
				$loginPesquisa = $_POST["loginPesquisa"];
				$tituloPesquisa = $_POST["tituloPesquisa"];
				$secaoPesquisa = $_POST["secaoPesquisa"];
				$zonaPesquisa = $_POST["zonaPesquisa"];			
			}
			$sqlWhere = "";
			//Busca por parâmetros
			if(!empty($nomePesquisa)){ 
				$sqlWhere = " WHERE e.nome like '%$nomePesquisa%' ";
			}
			if(!empty($loginPesquisa)){ 
				if(empty($sqlWhere)) 
					$sqlWhere = "WHERE u.login = '$loginPesquisa' ";
				else
					$sqlWhere += "AND u.login = '$loginPesquisa' ";
			}
			if(!empty($secaoPesquisa)){ 
				if(empty($sqlWhere)) 
					$sqlWhere = "WHERE e.secao = '$secaoPesquisa' ";
				else
					$sqlWhere .= "AND e.secao = '$secaoPesquisa' ";
			}
			if(!empty($zonaPesquisa)){ 
				if(empty($sqlWhere)) 
					$sqlWhere = "WHERE e.zona = '$zonaPesquisa' ";
				else
					$sqlWhere .= "AND e.zona = '$zonaPesquisa' ";
			}
			if(!empty($tituloPesquisa)){ 
				if(empty($sqlWhere)) 
					$sqlWhere = "WHERE e.titulo = '$tituloPesquisa' ";
				else
					$sqlWhere .= "AND e.titulo = '$tituloPesquisa' ";
			}			
			//SQL de Busca
			$sqlPesquisaUsr = "SELECT e.nome, u.login, e.nascimento, e.cep, e.zona, e.secao, e.titulo, e.idUsuario, u.id  FROM usuario u inner join eleitor e on u.id = e.idUsuario ".$sqlWhere;//nome ='' AND login ='' AND secao ='' AND zona = '' AND titulo ='';
			$resultPesquisaUsr = mysql_query($sqlPesquisaUsr);
			if ($resultPesquisaUsr === false) {
				echo "Erro na query ($sqlPesquisaUsr), do BD: " . mysql_error();
				exit;
			}
			if (mysql_num_rows($resultPesquisaUsr) > 0) {         				
				while ($row = mysql_fetch_array($resultPesquisaUsr, MYSQL_NUM)) {
					echo "
					<tr>
					<td>".$row[0]."</td>
					<td>".$row[1]."</td>
					<td>".$row[2]."</td>
					<td>".$row[3]."</td>
					<td>".$row[4]."</td>
					<td>".$row[5]."</td>
					<td>".$row[6]."</td>
					<td>
						<a class=\"btn btn-link\" href=\"cadastroEleitor.php?action=editar&id=".$row[7]."\">Editar</a> / 
						<a class=\"btn btn-link\" onclick=\"return confirmarRemover();\" href=\"cadastroEleitor.php?action=excluir&id=".$row[7]."\">Excluir</a>
					</td>
					</tr>";
					}
			}
		}
		?>
		</table>
	</form>	
</body>
</html>
