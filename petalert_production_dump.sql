-- =========================================================
-- PetAlert - Plataforma de Proteção & Adoção de Animais
-- Dump de Produção Estruturado Sem Conflito de Chaves (Ordem Dependente)
-- Compatível com InfinityFree, phpMyAdmin, MySQL 5.7 e 8.0
-- Data de Geração: 2026-11-10 16:50:41
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET NAMES utf8mb4;

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_usuario`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_usuario`;
CREATE TABLE `tb_usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `rua` varchar(150) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(10) DEFAULT NULL,
  `idade` int(11) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1,
  `foto` varchar(255) DEFAULT NULL,
  `tipo_usuario` varchar(20) DEFAULT 'adotante',
  `status_identidade` varchar(25) NOT NULL DEFAULT 'nao_verificado',
  `data_verificacao_identidade` datetime DEFAULT NULL,
  `protocolo_identidade` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `tb_usuario` (29 registros)
INSERT INTO `tb_usuario` VALUES
('2', 'Gabriel Oliveira', '11987654321', 'Rua Augusta, 125', 'Rua Augusta', '125', 'Centro', 'São Paulo', 'SP', '18', 'Gabriel123', 'GabrielOliveira@alertemail.com', '2000-01-01', '00000000002', '17500-000', '2026-09-19 12:05:29', '1', '0', 'doador', 'pendente', NULL, NULL),
('4', 'Guilherme Carlos', '11987654322', 'Avenida Paulista, 842', 'Avenida Paulista', '842', 'Bela Vista', 'São Paulo', 'SP', '22', '$2y$10$fMPq6ajqVGk42NXSGV8SOO2UDDiQVq.tGSnaXoBn3TBY8OngvWlte', 'maria@alertemail.com', '2000-01-01', '00000000004', '17500-000', '2026-09-19 12:05:29', '1', '0', 'doador', 'pendente', NULL, NULL),
('5', 'Carlos Silva', '11987654323', 'Avenida Brasil, 450', 'Avenida Brasil', '450', 'Jardim América', 'São Paulo', 'SP', '25', '$2y$10$/RduLEcim7FFTjdRpqYteuKLJtyvxqtPkNs1UHeakZn3GIGUCiYFC', 'carlos@alertemail.com', '2000-01-01', '00000000005', '17500-000', '2026-09-19 12:05:29', '1', '0', 'doador', 'pendente', NULL, NULL),
('6', 'Miguel Lima', '11987654324', 'Rua Vergueiro, 1260', 'Rua Vergueiro', '1260', 'Liberdade', 'São Paulo', 'SP', '21', '$2y$10$eToVDrS.5JUt9CJzOFOWDOTe4fHnPWR1ZhtCfPYDvkHpcWu0ujcjG', 'Miguellima@petalert.com', '2000-01-01', '00000000006', '17500-000', '2026-09-19 12:05:29', '1', '0', 'admin', 'pendente', NULL, NULL),
('7', 'Juliana Souza', '11987654325', 'Rua da Consolação, 578', 'Rua da Consolação', '578', 'Consolação', 'São Paulo', 'SP', '22', 'Juliana123', 'juliana@alertemail.com', '2000-01-01', '00000000007', '17500-000', '2026-09-19 12:05:29', '1', '0', 'doador', 'pendente', NULL, NULL),
('15', 'Ana Clara Adotante', '(11) 98765-4321', 'Avenida Paulista, 1000 - Bela Vista - São Paulo/SP', 'Avenida Paulista', '1000', 'Bela Vista', 'São Paulo', 'SP', '0', 'Ana123', 'anaclara@alertemail.com', '1995-04-12', '52998224725', '01310-100', '2026-10-19 16:27:22', '1', '0', 'adotante', 'pendente', NULL, NULL),
('16', 'Lucas Martins', '(14) 99876-5432', 'Rua Nove de Julho, 550 - Centro - Ourinhos/SP', 'Rua Nove de Julho', '550', 'Centro', 'Ourinhos', 'SP', '0', 'Lucas123\r\n', 'Lucas@alertemail.com', '1988-08-20', '43049633255', '19900-000', '2026-10-19 16:27:22', '1', '0', 'doador', 'pendente', NULL, NULL),
('20', 'Rafael Almeida', '(14) 99111-2222', 'Rua Nove de Julho, 550 - Centro - Ourinhos/SP', 'Rua Nove de Julho', '550', 'Centro', 'Ourinhos', 'SP', '0', 'Rafael123', 'RafaelMartins@alertemail.com', '1988-08-20', '13515395237', '19900-000', '2026-10-19 16:52:47', '1', '0', 'doador', 'pendente', NULL, NULL),
('22', 'Matheus Santos', '(14) 99111-2222', 'Rua Nove de Julho, 550 - Centro - Ourinhos/SP', 'Rua Nove de Julho', '550', 'Centro', 'Ourinhos', 'SP', '0', 'Matheus123', 'Matheus22@alertemail.com', '1988-08-20', '81844243370', '19900-000', '2026-10-19 16:54:49', '1', '0', 'doador', 'pendente', NULL, NULL),
('46', 'robson aparecido da silva', '(14) 99732-3472', 'Rua Servidor Claudio Marcelo Farina, 106 - Ourinhos Constante I - Ourinhos/SP', 'Rua Servidor Claudio Marcelo Farina', '106', 'Ourinhos Constante I', 'Ourinhos', 'SP', '43', 'robson123', 'robsonsilva728@gmail.com', '1983-05-08', '05795934607', '19904-716', '2026-11-01 17:47:11', '1', '../uploads/usuarios/usuario_46_6ae7a8c737f24.jpg', 'doador', 'pendente', NULL, NULL),
('56', 'Ana Martins', '14990000001', 'Rua das Acácias, 101 - Centro, Ourinhos/SP', 'Rua das Acácias', '101', 'Centro', 'Ourinhos', 'SP', '28', 'Ana123', 'ana.martins.teste@alertemail.com', '1998-02-14', '90000000001', '19900-000', '2026-11-03 09:36:09', '1', NULL, 'doador', 'pendente', NULL, NULL),
('57', 'Carlos Oliveira', '14990000002', 'Avenida Brasil, 220 - Centro, Ourinhos/SP', 'Avenida Brasil', '220', 'Centro', 'Ourinhos', 'SP', '32', 'Carlos123', 'carlos.oliveira.teste@alertemail.com', '1994-05-21', '90000000002', '19900-010', '2026-11-03 09:36:09', '1', NULL, 'doador', 'pendente', NULL, NULL),
('58', 'Pedro Almeida', '14990000003', 'Rua Paraná, 303 - Vila Nova, Ourinhos/SP', 'Rua Paraná', '303', 'Vila Nova', 'Ourinhos', 'SP', '30', 'Pedro123', 'pedro.almeida.teste@alertemail.com', '1996-01-10', '90000000003', '19900-020', '2026-11-03 09:36:09', '1', NULL, 'doador', 'pendente', NULL, NULL),
('59', 'Juliana Souza', '14990000004', 'Rua São Paulo, 404 - Centro, Ourinhos/SP', 'Rua São Paulo', '404', 'Centro', 'Ourinhos', 'SP', '29', 'Juliana123', 'juliana.souza.teste@alertemail.com', '1997-08-03', '90000000004', '19900-030', '2026-11-03 09:36:09', '1', NULL, 'doador', 'pendente', NULL, NULL),
('60', 'Mariana Costa', '14990000005', 'Rua das Flores, 505 - Jardim América, Ourinhos/SP', 'Rua das Flores', '505', 'Jardim América', 'Ourinhos', 'SP', '31', '$2y$10$Fut.hQzBWmiOK6MzCMXpweJetfvZimqE0HTELTjkKqmgk4FC7DUsK', 'mariana.costa.teste@alertemail.com', '1995-11-19', '90000000005', '19900-040', '2026-11-03 09:36:09', '1', NULL, 'doador', 'pendente', NULL, NULL),
('61', 'Rafael Mendes', '14990000006', 'Avenida dos Pioneiros, 606 - Vila Musa, Ourinhos/SP', 'Avenida dos Pioneiros', '606', 'Vila Musa', 'Ourinhos', 'SP', '33', 'Rafael123', 'rafael.mendes.teste@alertemail.com', '1993-04-08', '90000000006', '19900-050', '2026-11-03 09:36:10', '1', NULL, 'doador', 'pendente', NULL, NULL),
('62', 'Beatriz Lima', '14990000007', 'Rua Paraná, 707 - Centro, Ourinhos/SP', 'Rua Paraná', '707', 'Centro', 'Ourinhos', 'SP', '27', 'Beatriz123', 'beatriz.lima.teste@alertemail.com', '1999-06-12', '90000000007', '19900-060', '2026-11-03 09:36:10', '1', NULL, 'doador', 'pendente', NULL, NULL),
('63', 'Lucas Ferreira', '14990000008', 'Rua XV de Novembro, 808 - Centro, Ourinhos/SP', 'Rua XV de Novembro', '808', 'Centro', 'Ourinhos', 'SP', '34', 'Lucas123', 'lucas.ferreira.teste@alertemail.com', '1992-09-25', '90000000008', '19900-070', '2026-11-03 09:36:10', '1', NULL, 'doador', 'pendente', NULL, NULL),
('64', 'Isabela Rocha', '14990000009', 'Rua Minas Gerais, 909 - Centro, Ourinhos/SP', 'Rua Minas Gerais', '909', 'Centro', 'Ourinhos', 'SP', '26', 'Isabela123', 'isabela.rocha.teste@alertemail.com', '2000-03-17', '90000000009', '19900-080', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('65', 'Gabriel Santos', '14990000010', 'Rua Goiás, 1010 - Vila Nova, Ourinhos/SP', 'Rua Goiás', '1010', 'Vila Nova', 'Ourinhos', 'SP', '28', 'Gabriel123', 'gabriel.santos.teste@alertemail.com', '1998-07-11', '90000000010', '19900-090', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('66', 'Sofia Nascimento', '14990000011', 'Rua Pará, 1111 - Centro, Ourinhos/SP', 'Rua Pará', '1111', 'Centro', 'Ourinhos', 'SP', '25', 'Sofia123', 'sofia.nascimento.teste@alertemail.com', '2001-01-29', '90000000011', '19900-100', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('67', 'Mateus Ribeiro', '14990000012', 'Rua Ceará, 1212 - Jardim América, Ourinhos/SP', 'Rua Ceará', '1212', 'Jardim América', 'Ourinhos', 'SP', '29', 'Mateus123', 'mateus.ribeiro.teste@alertemail.com', '1997-12-05', '90000000012', '19900-110', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('68', 'Laura Carvalho', '14990000013', 'Rua Bahia, 1313 - Centro, Ourinhos/SP', 'Rua Bahia', '1313', 'Centro', 'Ourinhos', 'SP', '27', 'Laura123', 'laura.carvalho.teste@alertemail.com', '1999-10-16', '90000000013', '19900-120', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('69', 'Felipe Gomes', '14990000014', 'Rua Paraná, 1414 - Vila Musa, Ourinhos/SP', 'Rua Paraná', '1414', 'Vila Musa', 'Ourinhos', 'SP', '30', 'Felipe123', 'felipe.gomes.teste@alertemail.com', '1996-04-22', '90000000014', '19900-130', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('70', 'Camila Ferreira', '14990000015', 'Rua Amazonas, 1515 - Centro, Ourinhos/SP', 'Rua Amazonas', '1515', 'Centro', 'Ourinhos', 'SP', '26', 'Camila123', 'camila.ferreira.teste@alertemail.com', '2000-08-09', '90000000015', '19900-140', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('71', 'Bruno Cardoso', '14990000016', 'Rua Santa Catarina, 1616 - Centro, Ourinhos/SP', 'Rua Santa Catarina', '1616', 'Centro', 'Ourinhos', 'SP', '31', 'Bruno123', 'bruno.cardoso.teste@alertemail.com', '1995-02-27', '90000000016', '19900-150', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('72', 'Helena Barros', '14990000017', 'Rua Rio de Janeiro, 1717 - Centro, Ourinhos/SP', 'Rua Rio de Janeiro', '1717', 'Centro', 'Ourinhos', 'SP', '28', 'Helena123', 'helena.barros.teste@alertemail.com', '1998-11-30', '90000000017', '19900-160', '2026-11-03 09:36:10', '1', NULL, 'adotante', 'pendente', NULL, NULL),
('73', 'Verginia dos Santos Silva', '14982335171', 'Rua Servidor Claudio Marcelo Farina, 106, Ourinhos Constante I, Ourinhos - SP', 'Rua Servidor Claudio Marcelo Farina', '106', 'Ourinhos Constante I', 'Ourinhos', 'SP', '18', 'Verginia02', 'Verginia@petalert.com', '2008-09-02', '51257988867', '19904-716', '2026-11-10 10:34:10', '1', NULL, 'admin', 'nao_verificado', NULL, NULL),
('75', 'Maria Eduarda Dos Santos Pinto', '14 9968746237', NULL, 'Praça José Cardinali Mader no bairro Vila Boa Esperança', NULL, 'Boa esperança', 'ourinhos', 'SP', '17', 'Maria123', 'Mariaeduarda@petalert.com', '0000-00-00', '5534493770', '19912-283', '2026-11-10 10:09:01', '1', NULL, 'admin', 'nao_verificado', NULL, NULL);

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_animal`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_animal`;
CREATE TABLE `tb_animal` (
  `id_pet` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nome` varchar(50) NOT NULL,
  `idade` int(2) NOT NULL,
  `raca` varchar(100) DEFAULT NULL,
  `raça` varchar(50) NOT NULL,
  `peso` decimal(5,2) NOT NULL,
  `cor` varchar(50) NOT NULL,
  `porte` varchar(15) NOT NULL,
  `especie` varchar(30) NOT NULL,
  `tem_foto` tinyint(1) DEFAULT 0,
  `descricao` text DEFAULT NULL,
  `motivo_doacao` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT '../img/PetAlert-Logo.png',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1,
  `status_adocao` varchar(20) DEFAULT 'disponivel',
  PRIMARY KEY (`id_pet`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `tb_animal` (27 registros)
INSERT INTO `tb_animal` VALUES
('1', '2', 'Mimi', '2', 'Siamês', 'Siamês', '4.20', 'Branco e Cinza', 'Pequeno', 'Gato', '1', 'Gatinho dócil, castrado, muito carinhoso e pronto para um novo lar.', 'Mudança de cidade', '../img/mimi.jpg', '2026-09-19 12:05:29', '1', 'disponivel'),
('2', '4', 'Rex', '4', 'Pastor Alemão', 'Pastor Alemão', '32.00', 'Capa Preta', 'Grande', 'Cachorro', '1', 'Cão leal e muito brincalhão.', 'Falta de tempo', '../img/rex.jpg', '2026-09-19 12:05:29', '1', 'disponivel'),
('3', '5', 'Pipoca', '9', 'Poodle', 'Poodle', '6.80', 'Branco', 'Pequeno', 'Cachorro', '1', 'Cãozinho leal, brincalhão, saudável e ótimo companheiro.', 'Problemas financeiros', '../img/pipoca.jpg', '2026-09-19 12:05:29', '1', 'disponivel'),
('4', '6', 'Luna', '3', 'Persa', 'Persa', '3.80', 'Branco', 'Pequeno', 'Gato', '1', 'Gatinha Persa super dócil.', 'Alergia na família', '../img/luna.jpg', '2026-09-19 12:05:29', '1', 'disponivel'),
('5', '7', 'Bolinha', '1', 'Hamster Sírio', 'Hamster Sírio', '0.40', 'Caramel', 'Pequeno', 'Roedor', '1', 'Hamster super ativo e carinhoso.', 'Espaço pequeno', '../img/bolinha.jpg', '2026-09-19 12:05:29', '1', 'disponivel'),
('6', '4', 'Fofuxo', '1', 'Coelho Anão', 'Coelho Anão', '1.50', 'Cinza', 'Pequeno', 'Coelho', '1', 'Coelhinho manso.', 'Mudança de apartamento', '../img/fofuxo.jpg', '2026-09-19 12:05:29', '1', 'disponivel'),
('7', '5', 'Loro', '2', 'Papagaio', 'Papagaio', '0.90', 'Verde', 'Pequeno', 'Ave', '1', 'Animal dócil, saudável e pronto para adoção responsável.', 'Resgatado da rua, buscando tutor definitivo', '../img/loro.jpg', '2026-09-19 12:05:29', '1', 'disponivel'),
('8', '6', 'Thor', '3', 'Labrador', '', '0.00', 'Não informada', 'Grande', 'Cachorro', '1', 'Cão dócil, carinhoso e adora brincar na água.', 'Falta de tempo para passear com cão de grande porte', '../img/thor.jpg', '2026-09-19 12:15:55', '1', 'disponivel'),
('9', NULL, 'Pipoca', '2', 'Poodle Toy', 'Poodle Toy', '4.50', 'Branco Neve', 'Pequeno', 'Cachorro', '1', 'Cachorrinho carinhoso, castrado e vacinado.', 'Mudança para condomínio sem pets', '../uploads/pets/pipoca.jpg', '2026-09-26 18:54:21', '1', 'disponivel'),
('10', '13', 'Bolinha', '3', 'Siamês', 'Siamês', '4.00', 'Bege com manchas escuras', 'Pequeno', 'Gato', '1', 'Gatinho castrado, muito dócil e acostumado com apartamento.', 'Alergia na família', '../uploads/pets/bolinha.jpg', '2026-09-26 19:31:50', '0', 'inativo'),
('11', NULL, 'mel', '1', 'SRD', 'SRD', '6.00', 'amarelo claro e branca', 'Pequeno', 'Cachorro', '1', 'Mel é uma cachorra familiar na vizinhança, mas infelizmente vi um grupo de moleques malvados baterem nela, então decidi pegá-la e doá-la a uma pessoa responsável. Ela é amável e brincalhona.', 'Achei ela na rua, não tenho condições de ciala em casa', '../uploads/pets/pet_6ab854711d83b.jpg', '2026-09-26 20:25:37', '1', 'disponivel'),
('12', '13', 'Bolinha', '5', 'Siamês', 'Siamês', '4.00', 'Bege com manchas escuras', 'Pequeno', 'Gato', '1', 'Gatinho castrado, muito dócil e acostumado com apartamento.', 'Alergia na família', '../img/PetAlert-Logo.png', '2026-09-29 09:41:41', '0', 'inativo'),
('13', '1', 'Pipoca', '3', 'SRD', 'SRD', '12.50', 'Caramelo', 'Médio', 'Cachorro', '1', 'Cachorrinho dócil, brincalhão e castrado.', 'Mudança de cidade para apartamento pequeno.', '../img/PetAlert-Logo.png', '2026-10-19 16:27:22', '1', 'doado'),
('14', '1', 'Pipoca', '4', 'SRD', 'SRD', '13.00', 'Caramelo', 'Médio', 'Cachorro', '1', 'Cachorrinho dócil, castrado e vacinado.', 'Mudança de cidade para apartamento pequeno.', '../img/PetAlert-Logo.png', '2026-10-19 16:48:56', '1', 'doado'),
('15', '20', 'Pipoca', '3', 'SRD', 'SRD', '12.50', 'Caramelo', 'Médio', 'Cachorro', '1', 'Cachorrinho dócil, brincalhão e castrado.', 'Mudança de cidade para apartamento pequeno.', '../img/PetAlert-Logo.png', '2026-10-19 16:52:47', '1', 'doado'),
('16', '22', 'Pipoca', '4', 'SRD', 'SRD', '13.00', 'Caramelo', 'Médio', 'Cachorro', '1', 'Cachorrinho dócil, castrado e vacinado.', 'Mudança de cidade para apartamento pequeno.', '../img/PetAlert-Logo.png', '2026-10-19 16:54:49', '1', 'doado'),
('17', NULL, 'Pipoca', '4', 'SRD', 'SRD', '13.00', 'Caramelo', 'Médio', 'Cachorro', '1', 'Cachorrinho dócil, castrado e vacinado.', 'Mudança de cidade para apartamento pequeno.', '../img/PetAlert-Logo.png', '2026-10-19 17:00:05', '0', 'inativo'),
('18', NULL, 'Pipoca', '4', 'SRD', 'SRD', '13.00', 'Caramelo', 'Médio', 'Cachorro', '1', 'Cachorrinho dócil, castrado e vacinado.', 'Mudança de cidade para apartamento pequeno.', '../img/PetAlert-Logo.png', '2026-10-19 17:08:25', '0', 'inativo'),
('19', NULL, 'Pipoca', '4', 'SRD', 'SRD', '13.00', 'Caramelo', 'Médio', 'Cachorro', '1', 'Cachorrinho dócil, castrado e vacinado.', 'Mudança de cidade para apartamento pequeno.', '../img/PetAlert-Logo.png', '2026-11-01 14:45:05', '0', 'inativo'),
('28', '56', 'Amora', '2', 'SRD', 'SRD', '8.50', 'Caramelo', 'Pequeno', 'Cachorro', '0', 'Cadela dócil e sociável, acostumada ao convívio familiar.', 'Mudança para local que não aceita animais.', '../img/PetAlert-Logo.png', '2026-11-03 09:36:11', '1', 'disponivel'),
('29', '57', 'Toby', '3', 'Beagle', 'Beagle', '12.00', 'Tricolor', 'Médio', 'Cachorro', '0', 'Cão ativo e amigável, gosta de passeios e brincadeiras.', 'Mudança de rotina familiar.', '../img/PetAlert-Logo.png', '2026-11-03 09:36:11', '1', 'disponivel'),
('30', '58', 'Nina', '1', 'SRD', 'SRD', '3.20', 'Preta e branca', 'Pequeno', 'Gato', '0', 'Gatinha curiosa e carinhosa, acostumada a ambiente interno.', 'Responsável mudou para residência sem espaço adequado.', '../img/PetAlert-Logo.png', '2026-11-03 09:36:11', '1', 'disponivel'),
('31', '59', 'Mel', '4', 'Poodle', 'Poodle', '7.10', 'Branca', 'Pequeno', 'Cachorro', '0', 'Cadela tranquila, carinhosa e habituada a passeios.', 'Mudança para imóvel que não permite animais.', '../img/PetAlert-Logo.png', '2026-11-03 09:36:11', '1', 'disponivel'),
('32', '60', 'Simba', '2', 'Siamês', 'Siamês', '4.00', 'Creme e marrom', 'Pequeno', 'Gato', '1', 'Gato sociável, saudável e acostumado com pessoas.', 'Alergia de um familiar que passou a morar na residência.', '../uploads/pets/pet_6af22a5932dc9.jpg', '2026-11-03 09:36:11', '1', 'disponivel'),
('33', '61', 'Bidu', '5', 'SRD', 'SRD', '15.00', 'Marrom', 'Médio', 'Cachorro', '0', 'Cão adulto, companheiro e acostumado a viver em casa.', 'Dificuldade familiar para manter os cuidados necessários.', '../img/PetAlert-Logo.png', '2026-11-03 09:36:11', '1', 'disponivel'),
('34', '62', 'Lili', '2', 'Coelho Mini Lop', 'Coelho Mini Lop', '1.80', 'Cinza claro', 'Pequeno', 'Coelho', '0', 'Coelhinha calma, habituada a ambiente limpo e protegido.', 'Mudança para local onde não é possível manter o espaço adequado.', '../img/PetAlert-Logo.png', '2026-11-03 09:36:11', '1', 'disponivel'),
('35', '63', 'Pingo', '1', 'SRD', 'SRD', '0.30', 'Verde e amarelo', 'Pequeno', 'Ave', '0', 'Periquito criado em ambiente doméstico, com gaiola e acessórios próprios.', 'Responsável não consegue mais oferecer os cuidados diários.', '../img/PetAlert-Logo.png', '2026-11-03 09:36:11', '1', 'disponivel');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_veterinario`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_veterinario`;
CREATE TABLE `tb_veterinario` (
  `id_veterinario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(10) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `crmv` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `horario_atendimento` varchar(100) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_veterinario`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `tb_veterinario` (17 registros)
INSERT INTO `tb_veterinario` VALUES
('1', 'Clínica Vet Vida', '11987654321', 'Rua das Flores, 120 - Centro, São Paulo - SP', 'São Paulo', 'SP', '01001-000', NULL, NULL, 'Seg-Sex 08:00 - 18:00', 'https://www.google.com/maps/search/?api=1&query=Clinica+Vet+Vida+Sao+Paulo', NULL, NULL, NULL, '2026-09-19 12:05:30', '1'),
('2', 'Dr. João Silva', '11991234567', 'Av. Brasil, 450 - Jardins, São Paulo - SP', 'São Paulo', 'SP', '01415-000', NULL, NULL, 'Seg-Sáb 08:00 - 19:00', 'https://www.google.com/maps/search/?api=1&query=Dr+Joao+Silva+Veterinario+Sao+Paulo', NULL, NULL, NULL, '2026-09-19 12:05:30', '1'),
('3', 'Dra. Mariana Costa', '11999887766', 'Rua Verde, 88 - Centro, Ourinhos - SP', 'Ourinhos', 'SP', '19900-000', NULL, NULL, 'Seg-Sex 08:00 - 18:00', 'https://www.google.com/maps/search/?api=1&query=Dra+Mariana+Costa+Ourinhos', NULL, NULL, NULL, '2026-09-19 12:05:30', '1'),
('4', 'Pet Care Center', '11995554433', 'Av. dos Animais, 100 - Vila Nova, Ourinhos - SP', 'Ourinhos', 'SP', '19905-000', NULL, NULL, 'Seg-Sáb 08:00 - 20:00', 'https://www.google.com/maps/search/?api=1&query=Pet+Care+Center+Ourinhos', NULL, NULL, NULL, '2026-09-19 12:05:30', '1'),
('5', 'Clínica Animal Feliz', '11993332211', 'Rua Azul, 300 - Cascata, Marília - SP', 'Marília', 'SP', '17500-000', NULL, NULL, 'Seg-Sex 08:00 - 18:00', 'https://www.google.com/maps/search/?api=1&query=Clinica+Animal+Feliz+Marilia', NULL, NULL, NULL, '2026-09-19 12:05:30', '1'),
('6', 'SanClá Ourinhos - Clínica Veterinária 24h', '(14) 99849-0777', 'R. Monsenhor Córdova, 47 - Centro, Ourinhos - SP', 'Ourinhos', 'SP', '19900-011', NULL, NULL, 'Atendimento 24h', 'https://www.sanclaourinhos.com.br/', '-22.97900000', '-49.87000000', 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=600&auto=format&fit=crop', '2026-09-29 08:20:28', '1'),
('7', 'HVO - Hospital Veterinário Ourinhos', '(14) 3326-2164', 'Av. Conselheiro Rodrigues Alves, 236 - Centro, Ourinhos - SP', 'Ourinhos', 'SP', '19900-030', NULL, NULL, 'Seg-Sáb 08:00 - 20:00', 'https://hvo.com.br/', '-22.98100000', '-49.87200000', 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=600&auto=format&fit=crop', '2026-09-29 08:20:28', '1'),
('8', 'Centro Médico Veterinário de Ourinhos', '(14) 3026-2337', 'Rua Arlindo Luz, 738 - Centro, Ourinhos - SP', 'Ourinhos', 'SP', '19900-010', NULL, NULL, 'Seg-Sex 08:00 - 18:00', 'https://drresaliba.wixsite.com/cmvourinhos/home', '-22.97800000', '-49.86900000', 'https://images.unsplash.com/photo-1576201836106-db1758fd1c97?w=600&auto=format&fit=crop', '2026-09-29 08:20:28', '1'),
('9', 'Hospital Veterinário da USP (HOVET)', '(11) 3091-1244', 'Av. Prof. Dr. Orlando Marques de Paiva, 87 - Butantã, São Paulo - SP', 'São Paulo', 'SP', '05508-270', 'SP-0001', 'hovet@usp.br', 'Seg-Sex 07:00 - 17:00', 'https://vetsmart.com.br/hospital/usp', NULL, NULL, 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1'),
('10', 'Hospital Veterinário Sena Madureira 24h', '(11) 5572-8777', 'Rua Sena Madureira, 898 - Vila Mariana, São Paulo - SP', 'São Paulo', 'SP', '04021-001', 'SP-1234', 'contato@senamadureira.com.br', 'Atendimento 24h', 'https://senamadureira.com.br/', NULL, NULL, 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1'),
('11', 'Hospital Veterinário Escola UFRJ', '(21) 3938-1200', 'Av. Brigadeiro Trompowski, s/n - Ilha do Fundão, Rio de Janeiro - RJ', 'Rio de Janeiro', 'RJ', '21941-590', 'RJ-0099', 'hvet@ufrj.br', 'Seg-Sex 08:00 - 17:00', 'https://veterinaria.ufrj.br/', NULL, NULL, 'https://images.unsplash.com/photo-1576201836106-db1758fd1c97?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1'),
('12', 'Clínica Veterinária Botafogo 24h', '(21) 2537-8899', 'Rua Voluntários da Pátria, 215 - Botafogo, Rio de Janeiro - RJ', 'Rio de Janeiro', 'RJ', '22270-000', 'RJ-4521', 'atendimento@botafogovet.com.br', 'Atendimento 24h', 'https://www.google.com/maps/search/?api=1&query=Clinica+Veterinaria+Botafogo+Rio+de+Janeiro', NULL, NULL, 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1'),
('13', 'Hospital Veterinário da UFMG', '(31) 3409-2000', 'Av. Antônio Carlos, 6627 - Pampulha, Belo Horizonte - MG', 'Belo Horizonte', 'MG', '31270-901', 'MG-0112', 'vet@ufmg.br', 'Atendimento 24h (Plantão)', 'https://vet.ufmg.br/', NULL, NULL, 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1'),
('14', 'Hospital Veterinário da UFPR', '(41) 3350-5600', 'Rua dos Funcionários, 1540 - Cabral, Curitiba - PR', 'Curitiba', 'PR', '80035-050', 'PR-0789', 'hv@ufpr.br', 'Seg-Sex 08:00 - 18:00 / Plantão', 'https://agrarias.ufpr.br/hospital-veterinario/', NULL, NULL, 'https://images.unsplash.com/photo-1576201836106-db1758fd1c97?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1'),
('15', 'Hospital de Clínicas Veterinárias UFRGS', '(51) 3308-6100', 'Av. Bento Gonçalves, 9090 - Agronomia, Porto Alegre - RS', 'Porto Alegre', 'RS', '91540-000', 'RS-0443', 'hcv@ufrgs.br', 'Seg-Sex 08:00 - 18:00', 'https://www.ufrgs.br/favet/hcv/', NULL, NULL, 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1'),
('16', 'Hospital de Medicina Veterinária UFBA', '(71) 3283-6700', 'Av. Adhemar de Barros, 500 - Ondina, Salvador - BA', 'Salvador', 'BA', '40170-110', 'BA-0221', 'hospmev@ufba.br', 'Seg-Sex 07:30 - 17:00', 'https://medvet.ufba.br/', NULL, NULL, 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1'),
('17', 'Hospital Veterinário de Brasília (HVB) 24h', '(61) 3248-4000', 'SHIS QI 11 Bloco O - Lago Sul, Brasília - DF', 'Brasília', 'DF', '71625-500', 'DF-0330', 'contato@hvb.com.br', 'Atendimento 24h', 'https://hvb.com.br/', NULL, NULL, 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=600&auto=format&fit=crop', '2026-10-19 15:19:56', '1');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_ia`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_ia`;
CREATE TABLE `tb_ia` (
  `id_ia` int(11) NOT NULL AUTO_INCREMENT,
  `pergunta` text NOT NULL,
  `resposta` text NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ia`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `tb_ia` (29 registros)
INSERT INTO `tb_ia` VALUES
('1', 'Olá Pet.IA! Quais os cuidados básicos que devo ter ao adotar um filhote de cachorro?', 'Olá! Que notícia maravilhosa! Adotar um filhote é o início de uma jornada cheia de amor, rabinhos abanando e momentos', 'gemini-3.5-flash', '2026-10-19 15:32:22'),
('2', 'Como posso doar um pet no PetAlert e quais os cuidados na alimentação de um filhote?', 'Olá! Que bom ter você por aqui! Sou a **Pet.IA**, a assistente virtual oficial da plataforma **PetAlert**, e estou super feliz em te ajudar! 🐾🐶🐱\n\nVou responder às suas duas dúvidas com muito carinho:\n\n---\n\n### 1. Como doar um pet na plataforma PetAlert? 🏡\n\nSe você precisa encontrar um lar amoroso e responsável para um animalzinho, o processo na nossa plataforma é bem simples e seguro:\n\n1. Acesse a aba **\'Doar pet\'** no menu da nossa plataforma.\n2. Preencha o cadastro com as informações do animal: **espécie, raça, idade e porte**.\n3. Adicione uma **foto bem bonita e clara** do pet (fotos ajudam muito a encontrar adotantes!).\n4. Informe o **motivo da doação**.\n\nAssim, nossa comunidade de protetores e adotantes conscientes poderá visualizar o perfil dele e encontrar um novo tutor cheio de amor para dar! ❤️\n\n---\n\n### 2. Quais os cuidados com a alimentação de um filhote? 🍼🍖\n\nOs filhotes estão em fase de crescimento acelerado e precisam de nutrientes específicos para se desenvolverem fortes e saudáveis:\n\n* **Ração específica para filhotes:** Ofereça sempre uma ração de boa qualidade (linha para filhotes da espécie dele), pois ela contém os níveis ideais de proteínas, vitaminas e minerais.\n* **Frequência das refeições:** Como o estômago do filhote é pequenininho, o ideal é fracionar a quantidade diária recomendada na embalagem em **3 a 4 refeições ao dia**.\n* **Água fresca e limpa:** Deixe sempre água limpa e fresca à disposição, trocando-a várias vezes ao dia.\n* **Transição gradual:** Caso precise mudar a marca da ração, faça isso aos poucos ao longo de 7 a 10 dias para evitar diarreias e desconfortos abdominais.\n* **Saúde em dia:** Não se esqueça de manter a **vacinação anual** (V8/V10 para cães ou V4/V5 para gatos, além da Raiva), a **vermifugação** e o controle de pulgas e carrapatos em dia! 💉✨\n\n---\n\n💡 **Lembrete importante:** Eu sou uma inteligência artificial e minhas dicas de cuidados são orientações gerais. Elas **nunca substituem a consulta e a avaliação clínica de um médico veterinário**! \n\nSe precisar encontrar profissionais na sua região, você pode pesquisar na nossa aba **\'Veterinário\'** informando sua cidade, estado ou CEP. 🏥✨\n\nSe tiver mais alguma dúvida, estou sempre por aqui para te ajudar! 🐾❤️', 'gemini-3.5-flash', '2026-10-19 15:41:25'),
('3', 'Olá Pet.IA! O que fazer se encontrar um gatinho abandonado?', 'Olá! Que atitude maravilhosa a sua em se preocupar e estender a mão para esse gatinho! 🐱🐾 Vou te guiar com os primeiros passos para garantir a segurança e o bem-estar dele:\n\n---\n\n### 1. 🚨 Verificação de Emerg', 'gemini-3.5-flash', '2026-10-19 15:42:28'),
('4', 'Olá Pet.IA! O que fazer se encontrar um gatinho abandonado?', 'Olá! Que atitude linda e cheia de amor! 🐱❤️ Encontrar e resgatar um gatinho abandonado faz toda a diferença na vida dele. Vou te ajudar com um', 'gemini-3.5-flash', '2026-10-19 15:43:39'),
('5', 'a mimi tem quantos anos?', 'Olá! 🐾 Que nome fofo! 🐱🐶\n\nComo eu sou a Pet.IA, assistente virtual da plataforma, não consigo consultar registros individuais ou inventar informações sobre um pet específico sem acessar a ficha dele no sistema. \n\nSe a **Mimi** for um pet que você viu disponível para adoção em nossa plataforma, você pode encontrar a idade dela acessando a aba **\'Pets\'**! Lá você consegue pesquisar pelos animais cadastrados e ver todos os detalhes como idade, porte, história e fotos. 💛\n\nAgora, se a Mimi é a sua companheirinha e você a cadastrou na plataforma, basta conferir o perfil dela na sua área do tutor!\n\nSe precisar de ajuda com mais alguma coisa na plataforma (como dicas de cuidados, vacinação ou busca por clínicas na aba **\'Veterinário\'**), é só me avisar! ✨', 'gemini-3.5-flash', '2026-10-19 15:49:42'),
('6', 'Oi Alex! Por que você está doando o Pipoca?', 'Oi! Olha, é uma decisão difícil para mim, mas estou colocando o Pipoca para adoção por conta de uma mudança de cidade para um apartamento pequeno. \n\nEle é um cachorrinho muito dócil e brincalhão, já é castrado, e eu quero muito encontrar um lar com bastante carinho para ele!', 'doador:gemini-3.5-flash', '2026-10-19 16:27:27'),
('7', 'ola queria saber o nome do pet', 'Olá! O nome dela é Mel. Ela é uma gracinha, muito amável e brincalhona! Você gostaria de saber mais alguma coisa sobre ela?', 'doador:gemini-3.5-flash-lite', '2026-10-19 16:32:48'),
('8', 'olá quantos anos ela tem?', 'Olá! A Luna tem 3 anos. Ela é uma gatinha Persa incrível e super dócil!', 'doador:gemini-3.5-flash', '2026-10-19 16:33:19'),
('9', 'O que fazer em caso de engasgo ou emergência com meu cachorro?', 'Olá! Sinto muito que você esteja passando por esse momento de aflição com o seu cãozinho 🚨🐶. \n\nComo o engasgo e emergências respiratórias são **SITUAÇÕES EXTREMAMENTE GRAVES E DE ALTO RISCO**, a orientação principal é:\n\n🚨 **LEVE SEU CACHORRO IMEDIATAMENTE A UM MÉDICO VETERINÁRIO OU HOSPITAL VETERINÁRIO 24 HORAS!** 🚨\n\n**Atenção importante:** Eu sou uma Inteligência Artificial ( Pet.IA ) e minhas dicas **NUNCA substituem a avaliação clínica e a intervenção urgente de um médico veterinário**. \n\n---\n\n### O que você pode tentar fazer A CAMINHO do veterinário (com muito cuidado):\n\n1. **Mantenha a calma:** O seu nervosismo pode deixar o pet ainda mais agitado, aumentando a frequência respiratória e piorando o engasgo.\n2. **Olhe a boca com cuidado:** Abra a boca dele com cuidado para ver se há algum objeto visível (como um brinquedo ou osso). \n   * **ATENÇÃO:** Só tente retirar o objeto se ele estiver **visível e fácil de pegar** com os dedos. **Nunca** empurre o objeto para o fundo nem coloque a mão se houver risco de ser mordido por reflexo ou dor.\n3. **Não ofereça água ou comida:** Isso pode piorar a obstrução das vias aéreas.\n4. **Posição:** Para cães pequenos, você pode tentar segurá-lo inclinando o corpo levemente para baixo para ajudar', 'geral:gemini-3.5-flash', '2026-10-19 16:36:31'),
('10', 'meu pet está caçando muitp', 'Olá! Entendo que esse comportamento do seu pet pode ser um pouco cansativo ou até preocupante no dia a dia 🐾🐶🐱.\n\nO instinto de caça é algo natural para muitos animais, especialmente para os cães (devido aos ancestrais lupinos) e para os gatos (que são predadores natos). No entanto, quando isso acontece com muita intensidade, precisamos direcionar essa energia da melhor forma para garantir a segurança dele e de outros animais.\n\nAqui vão algumas dicas carinhosas para ajudar:\n\n1. **Gaste a energia física e mental:** Muitas vezes, caçar é uma forma que o pet encontra para combater o tédio. Aumente os passeios diários (no caso dos cães) e brinque bastante com eles usando brinquedos interativos, bolinhas ou varinhas com penas (para gatos). Brinquedos de \"enriquecimento ambiental\", onde eles precisam caçar o próprio petisco, ajudam muito!\n2. **Ambiente seguro:** Se você tem um gatinho que tenta caçar pássaros na janela, certifique-se de que há telas de proteção firmes. Se for um cãozinho no quintal, verifique se não há acesso a bichinhos peçonhentos (como sapos ou insetos) que possam fazer mal a ele.\n3. **Treinamento e comandos:** Para os cães, ensinar comandos básicos como \"junto\", \"solta\" ou \"fica\" com reforço positivo (petiscos e carinho) é fundamental para conseguir controlar o impulso na hora do passeio.\n\nE lembre-se: se esse comportamento veio de repente e veio acompanhado de mudanças drásticas de humor, agitação extrema ou apatia, vale a pena dar uma passadinha no veterinário para garantir que está tudo bem com a saúde dele! 🩺❤️\n\nPor aqui na **PetAlert**, nós também cuidamos do bem-estar dos animais de várias formas! Caso precise de algo, veja só o que você pode fazer em nossa plataforma:\n* 🐾 **Aba \'Pets\':** Para conhecer animais que estão esperando por um lar amoroso, caso você queira aumentar a família com responsabilidade.\n* 🏡 **Aba \'Doar pet\':** Se precisar cadastrar um animal para doação, informando espécie, raça, idade, porte, foto e o motivo.\n* 💖 **Aba \'Doações\':** Onde você pode apoiar nossa causa fazendo doações financeiras via PIX ou doando insumos como ração, cobertores e medicamentos.\n* 🚨 **Aba \'Denúncias\':** Caso presencie maus-tratos, você pode relatar os fatos, o local e anexar fotos ou vídeos comprobatórios.\n\nSe precisar de mais dicas ou orientações, estou sempre por aqui para ajudar! 🐾✨', 'geral:gemini-3.5-flash-lite', '2026-10-19 16:36:52'),
('11', 'Oi Alex! Por que você está doando o Pipoca?', 'Oi! Olha, eu decidi colocar o Pipoca para adoção porque vou me mudar de cidade para um apartamento pequeno, e infelizmente o espaço não vai ser adequado para ele. \n\nEle é um cachorrinho super dócil, caramelo, porte médio, tem 4 anos e pesa 13 kg. Eu tenho muito carinho por ele e quero muito encontrar uma família bem legal que possa cuidar dele com todo o amor que ele merece!', 'doador:gemini-3.5-flash-lite', '2026-10-19 16:54:52'),
('12', 'Oi! Gostaria de saber mais sobre a rotina dele.', 'Olá, Alex! Tudo bem?\n\nEu me encantei muito pelo perfil do Pipoca e tenho bastante interesse em adotá-lo. Como vi que ele tem 4 anos e pesa 13 kg, queria saber um pouco mais sobre como é a rotina dele atualmente. \n\nEle é um cachorro que costuma gastar bastante energia durante o dia? Pergunto isso porque imagino que a mudança para um apartamento pequeno possa ter sido um desafio para ele, e eu gostaria de entender melhor como ele lida com passeios e com o tempo que passa dentro de casa.\n\nFico no aguardo para saber mais!', 'adotante:gemini-3.5-flash-lite', '2026-10-19 16:54:56'),
('13', 'Como funciona a denúncia de maus-tratos no PetAlert?', 'Olá! Que bom ter você por aqui! 🐾🐶🐱\n\nNa PetAlert, levamos a segurança e o bem-estar dos animais muito a sério. Fazer uma denúncia de maus-tratos pela plataforma é um ato de amor e proteção, e o processo é bem simples e seguro:\n\n1. Acesse a aba **\'Denúncias\'** na nossa plataforma.\n2. Relate os **fatos** com o máximo de detalhes possível sobre o que está acontecendo.\n3. Informe o **local** exato onde a situação está ocorrendo.\n4. Anexe **fotos ou vídeos** comprobatórios para ajudar na verificação da denúncia.\n\nA sua atitude faz toda a diferença para salvar vidas e proteger quem não pode se defender sozinho! \n\nSe precisar de mais alguma ajuda ou tiver outras dúvidas, estou sempre à disposição! 🐾❤️', 'geral:gemini-3.5-flash', '2026-10-19 16:55:02'),
('14', 'Oi Alex! Por que você está doando o Pipoca?', 'Oi! Que bom que você perguntou. \n\nInfelizmente, estou tendo que doar o Pipoca por causa de uma mudança de cidade para um apartamento pequeno. \n\nEle é um cachorrinho muito dócil, caramelo, porte médio, tem 4 anos e pesa 13 kg. Tenho muito carinho por ele e quero muito encontrar um lugar bem legal para ele ser feliz. \n\nVocê gostaria de saber mais alguma coisa sobre ele?', 'doador:gemini-3.5-flash-lite', '2026-10-19 17:00:10'),
('15', 'Oi! Gostaria de saber mais sobre a rotina dele.', 'Oi, Alex! Tudo bem?\n\nNa verdade, eu que adoraria saber um pouquinho mais sobre como é a rotina atual do Pipoca com você! \n\nComo vi que ele tem 4 aninhos e é esse caramelo lindo de porte médio, fiquei curiosa(o): como ele é no dia a dia? Ele costuma passear quantas vezes por dia? É mais calminho ou gosta bastante de', 'adotante:gemini-3.5-flash', '2026-10-19 17:00:16'),
('16', 'Como funciona a denúncia de maus-tratos no PetAlert?', 'Olá! Que importante a sua iniciativa. Proteger os animais e garantir o bem-estar deles é uma prioridade para todos nós! 🐾🐶🐱\n\nFazer uma denúncia de maus-tratos na plataforma **PetAlert** é um processo simples e seguro. Veja como funciona:\n\n1. Acesse a aba **\'Denúncias\'** no menu da nossa plataforma.\n2. **Relate os fatos:** Descreva com clareza e detalhes o que está acontecendo.\n3. **Informe o local:** Adicione o endereço correto ou pontos de referência de onde o animal se encontra.\n4. **Anexe comprovações:** Envie fotos ou vídeos que ajudem a evidenciar a situação de maus-tratos (isso é fundamental para que as medidas cabíveis sejam tomadas).\n\nDenunciar é um ato de amor e responsabilidade. A sua atitude pode salvar uma vida! 🚨❤️\n\nSe tiver qualquer outra dúvida, estou sempre aqui para te ajudar! 🐾', 'geral:gemini-3.5-flash', '2026-10-19 17:00:22'),
('17', 'ele é um otimo cachorro', 'Sim, dá para perceber que ele é maravilhoso! Eu me interessei muito pelo Thor. \n\nVi no perfil dele que você mencionou a falta de tempo para passear com ele devido ao porte grande, o que eu entendo perfeitamente, já que cães dessa raça realmente precisam de bastante espaço e exercícios. \n\nComo ele tem 3 anos e está nessa fase cheia de energia, você sabe me dizer como é o temperamento dele no dia a dia? Ele se dá bem com outras pessoas e costuma ser muito agitado dentro de casa?', 'adotante:gemini-3.5-flash-lite', '2026-10-19 17:05:51'),
('18', 'Oi Alex! Por que você está doando o Pipoca?', 'Oi! Então, infelizmente estou tendo que colocar o Pipoca para adoção por causa de uma mudança de cidade para um apartamento pequeno. \n\nÉ uma decisão bem difícil para mim, porque ele é um cachorrinho incrível, super dócil, já está castrado e vacinado. Só quero ter certeza de que ele vá para um lar onde receba todo o carinho que merece!', 'doador:gemini-3.5-flash', '2026-10-19 17:08:29'),
('19', 'Oi! Gostaria de saber mais sobre a rotina dele.', 'Oi, Alex! Tudo bem? Na verdade, eu é que adoraria saber um pouquinho mais sobre a rotina do Pipoca com você! Gostei muito dele, achei esse caramelo um fofo.\n\nComo costuma ser o dia a dia dele hoje? Ele passeia quantas vezes por dia, é mais agitado ou mais tranquilo? Gostaria de entender direitinho os hábitos dele para ver como podemos nos adaptar bem juntos!', 'adotante:gemini-3.5-flash', '2026-10-19 17:08:36'),
('20', 'Como funciona a denúncia de maus-tratos no PetAlert?', 'Olá! Que bom que você tocou nesse assunto. A proteção dos nossos animaizinhos é uma prioridade enorme para nós aqui no PetAlert 🐾❤️.\n\nSe você presenciou ou tem conhecimento de alguma situação de maus-tratos, fazer a denúncia na nossa plataforma é um processo simples e muito importante para salvar vidas. Funciona assim:\n\n1. **Acesse a aba \'Denúncias\':** No menu do PetAlert, procure pela seção específica de denúncias.\n2. **Relate os fatos:** Descreva com o máximo de detalhes o que está acontecendo.\n3. **Informe o local:** Coloque o endereço ou uma localização precisa de onde o animal se encontra.\n4. **Anexe provas:** Sempre que possível, adicione fotos ou vídeos comprobatórios. Isso ajuda muito a dar mais força e agilidade ao caso!\n\nLembre-se de que os animais não têm voz própria, então ser a voz deles é um ato de pura coragem e amor. \n\nSe o caso for uma **emergência médica grave** (como um animal ferido gravemente ou sofrendo risco iminente de morte), por favor, além de registrar no app, procure imediatamente ajuda policial (Ligue 190) ou um órgão de proteção animal local.\n\nEstou sempre por aqui se precisar de mais alguma orientação! 🐾✨', 'geral:gemini-3.5-flash-lite', '2026-10-19 17:08:42'),
('21', 'Oi Alex! Por que você está doando o Pipoca?', 'Oi! É com o coração apertado que estou colocando o Pipoca para adoção. O motivo é que estou de mudança de cidade para um apartamento pequeno e, infelizmente, não vou ter o espaço ideal para ele. \n\nEle é um cachorrinho muito dócil, e só quero garantir que ele encontre um novo lar onde seja muito amado e feliz!', 'doador:gemini-3.5-flash', '2026-11-01 14:45:10'),
('22', 'Oi! Gostaria de saber mais sobre a rotina dele.', 'Olá, Alex! Tudo bem?\n\nEu me apaixonei pelo Pipoca assim que vi a foto dele! Esse fofo de porte médio e tom caramelo me conquistou de primeira.\n\nComo vi no anúncio que você está doando por conta da mudança, gostaria muito de entender melhor como é a rotina atual dele para me preparar. Ele costuma passear quantas vezes por dia? Tem o costume de brincar bastante ou é mais calminho? \n\nTambém queria saber se ele se adapta bem com outros animais e como reage quando fica um tempo sozinho. Quero garantir que vou oferecer a melhor adaptação e todo o carinho que ele merece!', 'adotante:gemini-3.5-flash', '2026-11-01 14:45:18'),
('23', 'Como funciona a denúncia de maus-tratos no PetAlert?', 'Olá! Fico muito feliz em ver seu cuidado e preocupação com a proteção dos nossos amiguinhos! 🐾🐶🐱\n\nNa plataforma **PetAlert**, fazer uma denúncia de maus-tratos é um processo simples e fundamental para ajudarmos a proteger os animais. Funciona assim:\n\n1. Acesse a aba **\'Denúncias\'** no nosso aplicativo ou site.\n2. **Relate os fatos:** descreva detalhadamente a situação pela qual o animal está passando.\n3. **Informe a localização:** insira o endereço exato ou um ponto de referência de onde o pet se encontra.\n4. **Anexe provas:** envie fotos ou vídeos comprobatórios que ajudem a evidenciar os maus-tratos.\n\nQuanto mais informações precisas e evidências você fornecer, mais eficiente será o processo para ajudar o animalzinho em risco! \n\nSe precisar de mais alguma ajuda ou tiver outras dúvidas, estou sempre por aqui! ❤️🐾', 'geral:gemini-3.5-flash', '2026-11-01 14:45:24'),
('24', 'O que fazer em caso de engasgo ou emergência com meu cachorro?', 'Olá! Sinto muito que você e seu amiguinho estejam passando por essa situação! 🐾🚨\n\n⚠️ **ATENÇÃO E URGÊNCIA:**\nSe o seu cachorro está engasgado ou passando por qualquer outra emergência, **PROCURE IMEDIATAMENTE UM MÉDICO VETERINÁRIO OU UM HOSPITAL VETERINÁRIO 24 HORAS!** \n\nPor favor, lembre-se: **eu sou uma Inteligência Artificial e minhas orientações NUNCA substituem a avaliação e a intervenção clínica de um profissional veterinário.** Em casos de engasgo e falta de ar, cada segundo conta!\n\n---\n\n### O que fazer ENQUANTO você se desloca para o veterinário (Primeiros Socorros):\n\n1. **Mantenha a calma:** Seu nervosismo pode deixar o pet ainda mais agitado, o que aumenta o consumo de oxigênio e piora o engasgo.\n2. **Inspecione a boca com muito cuidado:**\n   * Abra a boca dele delicadamente e use uma luz para olhar a garganta.\n   * **ATENÇÃO:** Só tente retirar o objeto com os dedos se ele estiver **completamente visível e fácil de pegar**. Se estiver fundo, você pode acabar empurrando o objeto ainda mais para dentro ou levar uma mordida involuntária por dor/pânico do animal.\n3. **Manobra de desobstrução (para engasgo grave):**\n   * **Cães pequenos:** Pegue-o no colo, apoie as costas dele no seu peito e incline a cabeça dele para baixo. Faça 4 a 5 compressões suaves na barriga dele, logo abaixo das costelas, pressionando para dentro e para cima.\n   * **Cães grandes:** Posicione-se atrás do cão (em pé ou ajoelhado), abrace a cintura dele mantendo as mãos unidas na parte superior do abdômen (logo abaixo do osso do peito) e faça compressões firmes para dentro e para cima.\n   * Alternativamente, dar **leves tapas firmes nas costas** (entre as escápulas) com o cão inclinado para baixo pode ajudar a soltar o objeto.\n4. **NÃO dê água, leite ou comida:** Isso pode piorar a obstrução do ar ou ir para os pulmões.\n\nPor favor, peça ajuda a alguém para ir dirigindo enquanto você auxilia o seu cãozinho e **vá direto para a clínica 24h mais próxima!** 🐶❤️ \n\nEstou na torcida para que fique tudo bem com o seu patudinho! 🐾', 'geral:gemini-3.5-flash', '2026-11-01 14:55:45'),
('25', 'qual a idade dele?', 'O Loro tem 2 anos! É um papagaio bem novinho, dócil e cheio de vida.', 'doador:gemini-3.5-flash', '2026-11-01 15:02:30'),
('26', 'Olá Pet.IA! O que fazer se encontrar um gatinho abandonado?', 'Olá! Que atitude linda e cheia de amor em ajudar esse gatinho! 🐾🐱 Ficamos muito felizes em ver o seu carinho e preocupação com os animais.\n\nPara te ajudar a cuidar dele da melhor forma, preparei um passo a passo:\n\n### 1. Avalie a saúde dele (Atenção Médica) 🏥\nPrimeiro, veja se o gatinho apresenta algum sinal de emergência, como ferimentos, sangramentos, fraqueza extrema, febre ou dor intensa.\n* **Importante:** Se ele estiver machucado ou doente, **procure imediatamente um médico veterinário ou hospital veterinário 24h**! \n* *Lembre-se:* Sou a Pet.IA, uma assistente virtual, e minhas orientações **nunca substituem a avaliação clínica de um profissional veterinário**.\n\n### 2. Cuidados Imediatos 🍼🥣\n* **Acolhimento e Aconchego:** Coloque o gatinho em um local seguro, calmo e aquecido (uma caixinha com panos ou cobertores limpos ajuda muito, pois eles sentem frio facilmente).\n* **Alimentação e Água:** Deixe água fresca sempre disponível. Ofereça ração adequada para a idade dele. *Atenção:* Se for um filhote muito pequenino, não dê leite de vaca (pode fazer mal). O ideal é buscar um substituto do leite materno para felinos em um pet shop ou clínica.\n\n### 3. Procure por um Tutor ou Encaminhe para Adoção 🏠\n* **Verifique se ele tem dono:** Ele pode ter apenas se perdido! Tente perguntar para vizinhos ou fazer publicações em redes sociais locais.\n* **Cadastre no PetAlert:** Se você não puder ficar com ele e não encontrar o antigo tutor, nós podemos te ajudar! Acesse a aba **\"Doar pet\"** na nossa plataforma. Lá você poderá cadastrar a espécie, idade aproximada, porte, fotos bonitas dele e contar a história. Assim, milhares de pessoas poderão ver o perfil dele para uma **Adoção Responsável**! ❤️\n* **Adoção Responsável:** Se você estiver pensando em ficar com o gatinho (o que seria maravilhoso!), lembre-se de refletir sobre ter tempo, espaço, carinho e recursos para os cuidados de rotina, vacinas e castração.\n\nSe precisar de mais informações sobre cuidados, doações de insumos ou qualquer outra ajuda, estou sempre por aqui! Muito obrigada por fazer a diferença na vida desse gatinho! 🐾✨', 'geral:gemini-3.5-flash', '2026-11-01 14:42:17'),
('27', 'Olá Pet.IA! O que fazer se encontrar um gatinho abandonado?', 'Olá! Que bom ter você por aqui! Fico muito feliz em saber que você está preocupado(a) em ajudar esse bichinho. A sua atitude faz toda a diferença! 🐾🐱❤️\n\nSe você encontrou um gatinho abandonado, aqui está um passo a passo do que fazer para garantir o bem-estar dele:\n\n---\n\n### 1. 🚨 Avaliação de Saúde e Emergência\nAntes de tudo, verifique o estado do gatinho. \n* **Atenção:** Se ele estiver machucado, sangrando, muito fraco, prostrado, com dificuldade para respirar ou hipotérmico, **procure imediatamente um Médico Veterinário ou Hospital Veterinário 24h!** \n* *Lembre-se:* Eu sou a Pet.IA, uma inteligência artificial, e minhas orientações **nunca substituem a avaliação clínica de um profissional veterinário**, que é essencial, especialmente para animais resgatados da rua.\n\n---\n\n### 2. 📦 Acolhimento e Cuidados Básicos\nSe o gatinho estiver aparentemente bem ou enquanto aguarda a consulta:\n* **Aquecimento:** Se for um filhote, mantenha-o aquecido em uma caixinha com toalhas macias (filhotes perdem calor muito rápido).\n* **Alimentação e Água:** Ofereça água limpa e fresca. Se ele já comer sólidos, ofereça ração própria para a faixa etária (ou sachê para filhotes). *Nunca ofereça leite de vaca*, pois pode causar diarreia grave.\n* **Espaço Seguro:** Deixe-o em um local tranquilo, protegido de correntes de ar, barulho e de outros animais que você possa ter em casa.\n\n---\n\n### 3. 🔍 Verificar se ele tem tutor\nÀs vezes o gatinho apenas se perdeu! Pergunte aos vizinhos, procure em grupos do bairro e verifique se há anúncios de animais perdidos.\n\n---\n\n### 4. 🏠 O que fazer a seguir (Adoção ou Encontrar um Lar)\n* **Se você puder adotar:** Que notícia maravilhosa! Lembre-se de levá-lo ao veterinário para vacinação, vermifugação e agendamento da castração. \n* **Se você não puder ficar com ele:** Você pode usufruir da nossa plataforma **PetAlert**! Acesse a aba **\'Doar pet\'** para cadastrar o gatinho (informando espécie, idade aproximada, porte, fotos bonitas e o motivo do cadastro). Assim, pessoas interessadas em uma **Adoção Responsável** poderão encontrá-lo na nossa aba **\'Pets\'**! 🐾\n\nMuito obrigada por proteger esse gatinho! Se precisar de mais dicas de cuidados, estou sempre por aqui! 🐱✨', 'geral:gemini-3.5-flash', '2026-11-01 15:00:10'),
('28', 'PARA VIROSE EM FILHOTE TEM CURA?', 'Olá! Entendo perfeitamente a sua preocupação. Ver um filhotinho doente dá um aperto no coração, e esse é um momento que exige muita atenção e rapidez! 🐾🚨\n\n⚠️ **ALERTA DE EMERGÊNCIA:**\nViroses em filhotes (como a Parvovirose e a Cinomose) são **doenças extremamente graves e que progridem muito rápido**. Se o seu filhote está com suspeita ou sintomas de virose (como diarreia, vômito, prostração ou falta de apetite), **LEVE-O IMEDIATAMENTE A UM MÉDICO VETERINÁRIO OU HOSPITAL VETERINÁRIO 24H!** \n\n*(Lembre-se sempre: eu sou a Pet.IA, uma Inteligência Artificial, e minhas orientações NUNCA substituem a avaliação e o diagnóstico clínico de um profissional veterinário).*\n\n---\n\n### **A virose em filhotes tem cura?**\n\n**SIM! A virose tem cura e o filhote pode se recuperar totalmente!** ❤️🐶\n\nNo entanto, a chance de cura depende quase que exclusivamente de **quão rápido o tratamento veterinário é iniciado**. \n\n**Como funciona o tratamento:**\n* **Não existe um \"remédio mágico\" que mata o vírus instantaneamente:** O tratamento consiste em dar **suporte intensivo** ao organismo do filhote para que o próprio sistema imunológico dele consiga combater e vencer o vírus.\n* **Combate à desidratação:** O maior perigo para um filhote é a desidratação rápida causada por vômitos e diarreia. No veterinário, ele receberá soro na veia (hidratação e nutrientes).\n* **Medicamentos de suporte:** O veterinário usará remédios para cessar os vômitos, controlar a dor, protetores gástricos e antibióticos (para evitar que bactérias aproveitem a imunidade baixa).\n\n---\n\n### 🚨 **Cuidados importantes AGORA:**\n1. **NÃO medique por conta própria:** Medicamentos humanos ou caseiros podem ser fatais para um filhote debilitado.\n2. **Isole o filhote de outros cães:** As viroses caninas são altamente contagiosas. Se tiver outros pets em casa, mantenha-os separados e higienize o ambiente com água sanitária/desinfetante próprio.\n3. **Ofereça aquecimento:** Filhotes doentes perdem calor corporal muito rápido. Mantenha-o aquecido com cobertores enquanto vai ao veterinário.\n\nCorra para a clínica veterinária mais próxima! Quanto mais cedo ele receber a medicação e o soro, maiores são as chances de ele se curar e voltar a brincar e correr! \n\nEstou torcendo muito pela recuperação do seu bebezinho! 🐾❤️', 'geral:gemini-3.5-flash', '2026-11-01 17:36:19'),
('29', 'Olá Pet.IA! O que fazer se encontrar um gatinho abandonado?', 'Olá! Que atitude linda a sua! 🐱✨ É muito nobre estender a mão para um bichinho que precisa de ajuda. Vou te orientar com os primeiros passos para cuidar desse gatinho em segurança!\n\n---\n\n### 🚨 1. Verificação de Saúde e Emergências\nAntes de tudo, observe o estado do gatinho:\n* Se ele estiver **machucado, sangrando, muito prostrado, com respiração difícil, vômitos ou sinais de envenenamento/dor intensa**, **PROCURE IMEDIATAMENTE UM MÉDICO VETERINÁRIO OU HOSPITAL VETERINÁRIO 24H!** 🏥🚨\n* ⚠️ **Atenção:** Eu sou a Pet.IA, uma inteligência artificial. Minhas orientações são para cuidados gerais e **nunca substituem a avaliação clínica de um profissional veterinário!**\n\n---\n\n### 🏠 2. Primeiros Cuidados (Conforto e Alimentação)\nSe o gatinho estiver aparentemente bem, apenas assustado ou com fome:\n* **Achego e Segurança:** Coloque-o em um local calmo, quentinho e protegido (uma caixa de papelão com uma toalha macia ou cobertor é ideal).\n* **Água e Comida:** Deixe **água fresca** sempre disponível. \n  * Se for um **filhote muito pequeno** (olhos recém-abertos), procure um substituto de leite materno felino em um pet shop. **Nunca dê leite de vaca**, pois causa sérios diarreias e desidratação.\n  * Se já comer sozinho, ofereça ração própria para a faixa etária dele (ou sachê para gatos).\n* **Higiene:** Deixe uma caixinha com areia sanitária por perto. Os gatos costumam usar instintivamente!\n\n---\n\n### 🔍 3. O gatinho tem dono?\nAntes de colocá-lo para doação, pergunte aos vizinhos se alguém perdeu um gato e confira se ele usa coleira ou identificação.\n\n---\n\n### 🐾 4. Como a plataforma PetAlert pode te ajudar:\n\n* **Se você NÃO puder ficar com ele e quiser encontrar um lar:**\n  Acesse a aba **\"Doar pet\"** aqui na plataforma! Lá você pode cadastrar a espécie, raça (ou SRD/sem raça definida), idade aproximada, porte, fotos bem bonitas e o motivo da doação. Assim ajudamos a encontrar uma família responsável para ele.\n\n* **Se você (ou alguém que você conhece) quiser adotar:**\n  Acesse a aba **\"Pets\"** para ver todos os animais incríveis que estão aguardando um lar amoroso. Lembre-se sempre da **Adoção Responsável**: pense no tempo, espaço, carinho e custos com vacinas e ração que o pet precisará ao longo da vida! 💙\n\n* **Quer ajudar a causa animal?**\n  Na nossa aba **\"Doações\"**, é possível fazer contribuições financeiras via PIX ou doar insumos (como ração, medicamentos e cobertores) para ajudar protetores e pets necessitados.\n\n---\n\nMuito obrigada por proteger esse gatinho! Se precisar de mais alguma orientação, estou sempre por aqui! 🐾🐱 quentinhos abraços!', 'geral:gemini-3.5-flash', '2026-11-01 18:06:38');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_doacao`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_doacao`;
CREATE TABLE `tb_doacao` (
  `id_doacao` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nome_doador` varchar(100) DEFAULT NULL,
  `tipo_doacao` varchar(20) NOT NULL DEFAULT 'financeira',
  `data_doacao` date NOT NULL,
  `valor_doado` decimal(10,2) NOT NULL,
  `objeto_doado` varchar(100) NOT NULL,
  `quantidade` varchar(50) DEFAULT NULL,
  `contato` varchar(100) DEFAULT NULL,
  `status_pagamento` varchar(30) NOT NULL DEFAULT 'aguardando_pagamento',
  `txid` varchar(64) DEFAULT NULL,
  `codigo_pix` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `tamanho` varchar(60) DEFAULT NULL,
  `unidade_medida` varchar(40) DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `comprovante_caminho` varchar(255) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_entrega` datetime DEFAULT NULL,
  PRIMARY KEY (`id_doacao`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `tb_doacao` (45 registros)
INSERT INTO `tb_doacao` VALUES
('1', '2', 'Maria Silva', 'insumo', '2026-06-06', '0.00', 'Ração para cães', '2 pacotes', NULL, 'confirmado', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('2', '2', 'Maria Silva', 'financeira', '2026-06-07', '50.00', 'Doação financeira (PIX)', NULL, NULL, 'pago', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('3', '2', 'João Santos', 'financeira', '2026-06-08', '20.00', 'Doação financeira (PIX)', NULL, NULL, 'pago', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('4', '2', 'João Santos', 'insumo', '2026-06-03', '0.00', 'Cobertor', '1 unidade', NULL, 'confirmado', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('5', '2', 'Usuário anônimo', 'insumo', '2026-06-04', '0.00', 'Medicamentos veterinários', '1 caixa', NULL, 'confirmado', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('6', '2', NULL, 'financeira', '2026-06-05', '0.00', 'Brinquedos', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('7', '2', NULL, 'financeira', '2026-06-11', '100.00', 'Doação via PIX', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('8', '2', NULL, 'financeira', '2026-09-19', '25.00', 'Cobertores', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('9', '2', NULL, 'financeira', '2026-09-19', '0.00', 'Cobertores', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('10', '10', NULL, 'financeira', '2026-06-12', '0.00', 'Caixa de Transporte P', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('11', '11', NULL, 'financeira', '2026-06-13', '150.00', 'Doação via PIX', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('12', '12', NULL, 'financeira', '2026-06-14', '0.00', 'Coleiras e Guias', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('13', '13', NULL, 'financeira', '2026-06-15', '75.00', 'Doação via Cartão', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('17', '7', NULL, 'financeira', '2026-06-15', '75.00', 'Doação via Cartão', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('18', '8', NULL, 'financeira', '2026-06-16', '0.00', 'Comedouro e Bebedouro', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('19', '9', NULL, 'financeira', '2026-06-17', '200.00', 'Doação via PIX', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('20', '4', NULL, 'financeira', '2026-06-18', '50.00', 'Doação via PIX', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('21', '5', NULL, 'financeira', '2026-06-19', '0.00', 'Ração para Gatos 10kg', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('22', '6', NULL, 'financeira', '2026-06-20', '120.00', 'Doação via PIX', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('23', '7', NULL, 'financeira', '2026-06-21', '0.00', 'Caminha Pet GG', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('28', '5', NULL, 'financeira', '2026-06-20', '120.00', 'Doação via PIX', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('29', '6', NULL, 'financeira', '2026-06-21', '0.00', 'Caminha Pet GG', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('30', '7', NULL, 'financeira', '2026-06-22', '200.00', 'Doação via PIX', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('31', '8', NULL, 'financeira', '2026-06-23', '0.00', 'Shampoo e Antipulgas', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('32', '9', NULL, 'financeira', '2026-06-24', '80.00', 'Doação via Cartão', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('33', '2', NULL, 'financeira', '2026-09-19', '0.00', 'cobertor', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('34', '2', NULL, 'financeira', '2026-10-19', '12.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('35', NULL, 'Usuário anônimo', 'financeira', '2026-10-19', '10.00', 'Doação financeira (PIX)', NULL, NULL, 'pago', NULL, NULL, '2026-10-19 15:19:56', NULL, NULL, NULL, NULL, NULL, NULL),
('36', NULL, 'Carlos Visitante Teste', 'financeira', '2026-10-19', '35.00', 'Doação financeira (PIX)', NULL, NULL, 'pago', 'PET6644C3935F', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540535.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET6644C3935F6304E809', '2026-10-19 15:41:16', NULL, NULL, NULL, NULL, NULL, NULL),
('37', NULL, 'Ana Visitante', 'insumo', '2026-10-19', '0.00', 'Ração Premium para Gatos 10kg', '3 pacotes', '(14) 99999-8888', 'confirmado', NULL, NULL, '2026-10-19 15:41:16', NULL, NULL, NULL, NULL, NULL, NULL),
('38', '4', 'Maria Eduarda', 'financeira', '2026-10-19', '50.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', 'PET6644C40A65', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540550.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET6644C40A656304B172', '2026-10-19 15:41:16', NULL, NULL, NULL, NULL, NULL, NULL),
('39', NULL, 'Visitante PIX Teste', 'financeira', '2026-10-19', '45.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', 'PET6648A6735E', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540545.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET6648A6735E63044473', '2026-10-19 15:42:18', NULL, NULL, NULL, NULL, NULL, NULL),
('40', NULL, 'Carlos Insumos', 'insumo', '2026-10-19', '0.00', 'Cobertor Térmico Pet', '2 unidades', NULL, 'confirmado', NULL, NULL, '2026-10-19 15:42:20', NULL, NULL, NULL, NULL, NULL, NULL),
('41', NULL, 'Visitante PIX Teste', 'financeira', '2026-10-19', '45.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', 'PET664D0DCE76', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540545.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET664D0DCE7663043361', '2026-10-19 15:43:28', NULL, NULL, NULL, NULL, NULL, NULL),
('42', NULL, 'Carlos Insumos', 'insumo', '2026-10-19', '0.00', 'Cobertor Térmico Pet', '2 unidades', NULL, 'confirmado', NULL, NULL, '2026-10-19 15:43:31', NULL, NULL, NULL, NULL, NULL, NULL),
('43', '9', 'Virginia Santos', 'financeira', '2026-10-19', '34.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', 'PET67310BE7DC', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540534.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET67310BE7DC6304B2B1', '2026-10-19 16:44:16', NULL, NULL, NULL, NULL, NULL, NULL),
('44', '9', 'Usuário anônimo', 'insumo', '2026-10-19', '0.00', 'Caminha Pet', '1', '14982335171', 'confirmado', NULL, NULL, '2026-10-19 16:54:13', NULL, NULL, NULL, NULL, NULL, NULL),
('45', '9', 'Virginia Santos', 'financeira', '2026-10-19', '50.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', 'PET67C280675B', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540550.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET67C280675B630420C9', '2026-10-19 17:23:04', NULL, NULL, NULL, NULL, NULL, NULL),
('49', NULL, 'Verginia dos Santos Silva', 'financeira', '2026-11-01', '50.00', 'Doação financeira (PIX)', NULL, 'Usuário excluído (Anonimizado)', 'aguardando_pagamento', 'PET77ED48573D', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540550.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET77ED48573D63045C60', '2026-11-01 15:03:00', NULL, NULL, NULL, NULL, NULL, NULL),
('50', NULL, 'Visitante PIX Teste', 'financeira', '2026-11-01', '45.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', 'PET779EE55B9A', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540545.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET779EE55B9A63044E58', '2026-11-01 14:42:06', NULL, NULL, NULL, NULL, NULL, NULL),
('51', NULL, 'Carlos Insumos', 'insumo', '2026-11-01', '0.00', 'Cobertor Térmico Pet', '2 unidades', NULL, 'confirmado', NULL, NULL, '2026-11-01 14:42:07', NULL, NULL, NULL, NULL, NULL, NULL),
('54', NULL, 'Visitante PIX Teste', 'financeira', '2026-11-01', '45.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', 'PET77E202892B', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540545.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET77E202892B630456BB', '2026-11-01 15:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
('55', NULL, 'Carlos Insumos', 'insumo', '2026-11-01', '0.00', 'Cobertor Térmico Pet', '2 unidades', NULL, 'confirmado', NULL, NULL, '2026-11-01 15:00:00', NULL, NULL, NULL, NULL, NULL, NULL),
('58', NULL, 'Visitante PIX Teste', 'financeira', '2026-11-01', '45.00', 'Doação financeira (PIX)', NULL, NULL, 'aguardando_pagamento', 'PET7A9D037FBF', '00020101021226640014br.gov.bcb.pix0123contato@petalert.com.br0215DOACAO PETALERT520400005303986540545.005802BR5924PETALERT PROTECAO ANIMAL6008OURINHOS62170513PET7A9D037FBF63049085', '2026-11-01 18:06:24', NULL, NULL, NULL, NULL, NULL, NULL),
('59', NULL, 'Carlos Insumos', 'insumo', '2026-11-01', '0.00', 'Cobertor Térmico Pet', '2 unidades', NULL, 'confirmado', NULL, NULL, '2026-11-01 18:06:24', NULL, NULL, NULL, NULL, NULL, NULL);

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_doacao_dinheiro`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_doacao_dinheiro`;
CREATE TABLE `tb_doacao_dinheiro` (
  `id_doacao` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `tipo_pagamento` varchar(50) DEFAULT NULL,
  `chave_pix` varchar(255) DEFAULT NULL,
  `data_doacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_doacao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_doacao_pet`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_doacao_pet`;
CREATE TABLE `tb_doacao_pet` (
  `id_doacao_pet` int(11) NOT NULL AUTO_INCREMENT,
  `id_pet` int(11) DEFAULT NULL,
  `id_doador` int(11) DEFAULT NULL,
  `id_receptor` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'pendente',
  `data_doacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_doacao_pet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_doapet`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_doapet`;
CREATE TABLE `tb_doapet` (
  `id_usuario` int(11) NOT NULL,
  `id_pet` int(11) NOT NULL,
  `motivo_doacao` varchar(80) NOT NULL,
  `data_doacao` date NOT NULL,
  KEY `id_usuario` (`id_usuario`),
  KEY `id_pet` (`id_pet`),
  CONSTRAINT `tb_doapet_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuario` (`id_usuario`),
  CONSTRAINT `tb_doapet_ibfk_2` FOREIGN KEY (`id_pet`) REFERENCES `tb_animal` (`id_pet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `tb_doapet` (18 registros)
INSERT INTO `tb_doapet` VALUES
('2', '1', 'Mudança de cidade', '2026-06-01'),
('4', '2', 'Falta de tempo', '2026-06-03'),
('5', '3', 'Problemas financeiros', '2026-06-05'),
('6', '4', 'Alergia na família', '2026-06-07'),
('7', '5', 'Espaço pequeno', '2026-06-10'),
('4', '6', 'Mudança de apartamento', '2026-09-20'),
('5', '7', 'Resgatado da rua, buscando tutor definitivo', '2026-09-21'),
('6', '8', 'Falta de tempo para passear com cão de grande porte', '2026-09-22'),
('20', '15', 'Mudança de cidade para apartamento pequeno.', '2026-10-19'),
('22', '16', 'Mudança de cidade para apartamento pequeno.', '2026-10-19'),
('56', '28', 'Mudança para local que não aceita animais.', '2026-11-03'),
('57', '29', 'Mudança de rotina familiar.', '2026-11-03'),
('58', '30', 'Responsável mudou para residência sem espaço adequado.', '2026-11-03'),
('59', '31', 'Mudança para imóvel que não permite animais.', '2026-11-03'),
('60', '32', 'Alergia de um familiar que passou a morar na residência.', '2026-11-03'),
('61', '33', 'Dificuldade familiar para manter os cuidados necessários.', '2026-11-03'),
('62', '34', 'Mudança para local onde não é possível manter o espaço adequado.', '2026-11-03'),
('63', '35', 'Responsável não consegue mais oferecer os cuidados diários.', '2026-11-03');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_chat`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_chat`;
CREATE TABLE `tb_chat` (
  `id_chat` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_pet` int(11) DEFAULT NULL,
  `id_usuario_origem` int(11) DEFAULT NULL,
  `id_usuario_destino` int(11) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_mensagem` timestamp NOT NULL DEFAULT current_timestamp(),
  `lida` tinyint(1) DEFAULT 0,
  `tipo_conversa` varchar(20) DEFAULT 'doador',
  PRIMARY KEY (`id_chat`),
  KEY `idx_chat_pet` (`id_pet`),
  CONSTRAINT `fk_tb_chat_pet` FOREIGN KEY (`id_pet`) REFERENCES `tb_animal` (`id_pet`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `tb_chat` (39 registros)
INSERT INTO `tb_chat` VALUES
('3', '9', '4', '9', '6', 'quantos anos tem??', '2026-09-20 18:02:56', '2026-09-20 18:02:56', '0', 'doador'),
('4', '6', '4', '6', '9', 'Não consegui obter uma resposta no momento.', '2026-09-20 18:02:56', '2026-09-20 18:02:56', '0', 'doador'),
('5', '9', '4', '9', '6', 'qual o nome do pet?', '2026-09-20 18:03:09', '2026-09-20 18:03:09', '0', 'doador'),
('6', '6', '4', '6', '9', 'Não consegui obter uma resposta no momento.', '2026-09-20 18:03:09', '2026-09-20 18:03:09', '0', 'doador'),
('7', '9', '4', '9', '6', 'quantos anos tem??', '2026-09-20 18:07:39', '2026-09-20 18:07:39', '0', 'doador'),
('8', '9', '4', '9', '6', 'qual o nome?', '2026-09-20 18:18:05', '2026-09-20 18:18:05', '0', 'doador'),
('9', '9', '4', '9', '6', 'qual a idade?', '2026-09-20 19:05:18', '2026-09-20 19:05:18', '0', 'doador'),
('13', '9', '8', '9', '6', 'ele é um cachorro?', '2026-09-29 14:28:27', '2026-09-29 14:28:27', '0', 'doador'),
('14', '6', '8', '6', '9', 'De acordo com as informações cadastradas para o(a) Thor, ele(a) é um(a) Cachorro Labrador, de porte Grande e tem 3 ano(s). Caso queira saber sobre o motivo da doação, histórico, peso ou cor, pode me perguntar!', '2026-09-29 14:28:27', '2026-09-29 14:28:27', '0', 'doador'),
('17', '9', '5', '9', '7', 'quantos anos ele tem?', '2026-10-05 20:41:48', '2026-10-05 20:41:48', '0', 'doador'),
('18', '9', '5', '7', '9', 'Oi! O(A) Bolinha tem 1 ano(s).', '2026-10-05 20:41:48', '2026-10-05 20:41:48', '0', 'doador'),
('19', '9', '5', '9', '7', 'oi quantos anos o bolinha tem?', '2026-10-05 20:43:08', '2026-10-05 20:43:08', '0', 'doador'),
('20', '9', '5', '7', '9', 'Oi! O(A) Bolinha tem 1 ano(s).', '2026-10-05 20:43:08', '2026-10-05 20:43:08', '0', 'doador'),
('21', '9', '4', '9', '6', 'oi quantos anos ela tem?', '2026-10-06 19:02:06', '2026-10-06 19:02:06', '0', 'doador'),
('22', '9', '4', '6', '9', 'A Luna tem 4 anos.', '2026-10-06 19:02:08', '2026-10-06 19:02:08', '0', 'doador'),
('23', '9', '5', '9', '7', 'Olá, ainda tenho muito interesse no Bolinha!', '2026-10-06 19:07:32', '2026-10-06 19:07:32', '0', 'doador'),
('24', '9', '5', '9', '7', 'Qual o melhor horário para eu ir buscá-lo?', '2026-10-06 19:07:32', '2026-10-06 19:07:32', '0', 'doador'),
('25', '9', '5', '9', '7', 'Olá, ainda tenho muito interesse no Bolinha!', '2026-10-06 19:07:56', '2026-10-06 19:07:56', '0', 'doador'),
('26', '9', '5', '9', '7', 'Qual o melhor horário para eu ir buscá-lo?', '2026-10-06 19:07:56', '2026-10-06 19:07:56', '0', 'doador'),
('27', '9', '4', '9', '6', 'qual o nome do pet?', '2026-10-06 19:09:44', '2026-10-06 19:09:44', '0', 'doador'),
('28', '9', '4', '6', '9', 'O nome dela é Luna.', '2026-10-06 19:09:56', '2026-10-06 19:09:56', '0', 'doador'),
('29', '9', '1', '9', '2', 'a mimi tem quantos anos?', '2026-10-19 20:49:36', '2026-10-19 20:49:36', '0', 'doador'),
('30', '2', '1', '2', '9', 'Olá! 🐾 Que nome fofo! 🐱🐶\n\nComo eu sou a Pet.IA, assistente virtual da plataforma, não consigo consultar registros individuais ou inventar informações sobre um pet específico sem acessar a ficha dele no sistema. \n\nSe a **Mimi** for um pet que você viu disponível para adoção em nossa plataforma, você pode encontrar a idade dela acessando a aba **\'Pets\'**! Lá você consegue pesquisar pelos animais cadastrados e ver todos os detalhes como idade, porte, história e fotos. 💛\n\nAgora, se a Mimi é a sua companheirinha e você a cadastrou na plataforma, basta conferir o perfil dela na sua área do tutor!\n\nSe precisar de ajuda com mais alguma coisa na plataforma (como dicas de cuidados, vacinação ou busca por clínicas na aba **\'Veterinário\'**), é só me avisar! ✨', '2026-10-19 20:49:42', '2026-10-19 20:49:42', '0', 'doador'),
('31', '1', '13', '1', '1', 'Olá, tenho interesse no Pipoca!', '2026-10-19 21:27:22', '2026-10-19 21:27:22', '0', 'doador'),
('32', '1', '13', '1', '1', 'Oi! Gostaria de saber como é o temperamento dele.', '2026-10-19 21:27:22', '2026-10-19 21:27:22', '0', 'adotante'),
('33', '9', '11', '9', NULL, 'ola queria saber o nome do pet', '2026-10-19 21:32:44', '2026-10-19 21:32:44', '0', 'doador'),
('34', '9', '11', '9', '9', 'Olá! O nome dela é Mel. Ela é uma gracinha, muito amável e brincalhona! Você gostaria de saber mais alguma coisa sobre ela?', '2026-10-19 21:32:48', '2026-10-19 21:32:48', '0', 'doador'),
('35', '9', '4', '9', '6', 'olá quantos anos ela tem?', '2026-10-19 21:33:16', '2026-10-19 21:33:16', '0', 'doador'),
('36', '6', '4', '6', '9', 'Olá! A Luna tem 3 anos. Ela é uma gatinha Persa incrível e super dócil!', '2026-10-19 21:33:19', '2026-10-19 21:33:19', '0', 'doador'),
('37', '1', '14', '1', '1', 'Olá Alex, tenho muito interesse no Pipoca!', '2026-10-19 21:48:56', '2026-10-19 21:48:56', '0', 'doador'),
('38', '1', '14', '1', '1', 'Oi! Gostaria de saber como ele reage com crianças.', '2026-10-19 21:48:56', '2026-10-19 21:48:56', '0', 'adotante'),
('39', '19', '15', '19', '20', 'Olá Alex, tenho muito interesse no Pipoca!', '2026-10-19 21:52:47', '2026-10-19 21:52:47', '0', 'doador'),
('40', '20', '15', '20', '19', 'Oi! Gostaria de saber como ele reage com crianças.', '2026-10-19 21:52:47', '2026-10-19 21:52:47', '0', 'adotante'),
('45', '9', '8', '9', '6', 'ele é um otimo cachorro', '2026-10-19 22:05:47', '2026-10-19 22:05:47', '0', 'adotante'),
('46', '6', '8', '6', '9', 'Sim, dá para perceber que ele é maravilhoso! Eu me interessei muito pelo Thor. \n\nVi no perfil dele que você mencionou a falta de tempo para passear com ele devido ao porte grande, o que eu entendo perfeitamente, já que cães dessa raça realmente precisam de bastante espaço e exercícios. \n\nComo ele tem 3 anos e está nessa fase cheia de energia, você sabe me dizer como é o temperamento dele no dia a dia? Ele se dá bem com outras pessoas e costuma ser muito agitado dentro de casa?', '2026-10-19 22:05:51', '2026-10-19 22:05:51', '0', 'adotante'),
('53', '4', '6', '4', '4', 'ola', '2026-11-03 18:48:08', '2026-11-03 18:48:08', '0', 'doador'),
('54', '4', '6', '4', '4', 'Oi! Eu sou o(a) Maria Eduarda, responsável pelo(a) Fofuxo. Ele(a) é um(a) Coelho Anão de 1 ano(s), porte Pequeno. O motivo da doação foi: Mudança de apartamento. Gostaria de saber mais algum detalhe sobre ele(a)?', '2026-11-03 18:48:09', '2026-11-03 18:48:09', '0', 'doador'),
('55', '4', '6', '4', '4', 'ola', '2026-11-03 18:48:21', '2026-11-03 18:48:21', '0', 'adotante'),
('56', '4', '6', '4', '4', 'Oi! Eu gostei bastante do(a) Fofuxo e gostaria muito de saber mais sobre a rotina dele(a). Ele(a) parece ser um companheiro incrível para a minha família!', '2026-11-03 18:48:21', '2026-11-03 18:48:21', '0', 'adotante');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_denuncia`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_denuncia`;
CREATE TABLE `tb_denuncia` (
  `id_denuncia` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_pet` int(11) DEFAULT NULL,
  `motivo` varchar(150) DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `descricao` text NOT NULL,
  `localizacao` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_denuncia`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `tb_denuncia` (5 registros)
INSERT INTO `tb_denuncia` VALUES
('1', NULL, '9', 'Maus-tratos e Abandono', 'Maus-tratos e Abandono', 'Animal amarrado em corrente curta sem água nem cobertura sob o sol.', 'Rua das Palmeiras, 300 - Centro', '../uploads/denuncias/prova_foto.jpg', NULL, 'em_analise', '2026-09-26 18:54:21'),
('2', NULL, NULL, 'Animal abandonado em terreno baldio', 'Animal abandonado em terreno baldio', 'Cãozinho de porte médio sem água nem abrigo.', 'Rua das Palmeiras, 120', '../uploads/denuncias/prova_foto_valida.jpg', NULL, 'pendente', '2026-10-19 16:55:02'),
('3', NULL, NULL, 'Animal abandonado em terreno baldio', 'Animal abandonado em terreno baldio', 'Cãozinho de porte médio sem água nem abrigo.', 'Rua das Palmeiras, 120', '../uploads/denuncias/prova_foto_valida.jpg', NULL, 'pendente', '2026-10-19 17:00:22'),
('4', NULL, NULL, 'Animal abandonado em terreno baldio', 'Animal abandonado em terreno baldio', 'Cãozinho de porte médio sem água nem abrigo.', 'Rua das Palmeiras, 120', '../uploads/denuncias/prova_foto_valida.jpg', NULL, 'pendente', '2026-10-19 17:08:42'),
('5', NULL, NULL, 'Animal abandonado em terreno baldio', 'Animal abandonado em terreno baldio', 'Cãozinho de porte médio sem água nem abrigo.', 'Rua das Palmeiras, 120', '../uploads/denuncias/prova_foto_valida.jpg', NULL, 'pendente', '2026-11-01 14:45:24');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_alerta`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_alerta`;
CREATE TABLE `tb_alerta` (
  `id_alerta` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `tipo_alerta` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `localizacao` varchar(255) DEFAULT NULL,
  `severidade` varchar(20) DEFAULT 'normal',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_alerta` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_alerta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_admin_permissao`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_admin_permissao`;
CREATE TABLE `tb_admin_permissao` (
  `id_permissao` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `nome_oficial` varchar(100) NOT NULL,
  `nivel` varchar(50) NOT NULL DEFAULT 'administrador_geral',
  `status` enum('ativo','pendente_dados','inativo') NOT NULL DEFAULT 'ativo',
  `observacoes` varchar(255) DEFAULT NULL,
  `data_concessao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_permissao`),
  UNIQUE KEY `nome_oficial` (`nome_oficial`),
  KEY `idx_admin_id_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `tb_admin_permissao` (3 registros)
INSERT INTO `tb_admin_permissao` VALUES
('1', '6', 'Miguel Lima', 'administrador_geral', 'ativo', 'Administrador oficial ativo (ID 6)', '2026-11-10 09:22:52'),
('2', '73', 'Verginia', 'administrador_geral', 'ativo', 'Administradora oficial ativa (IDs 73 e 74)', '2026-11-10 09:22:52'),
('3', '75', 'Maria Eduarda', 'administrador_geral', 'ativo', 'Administradora cadastrada e vinculada', '2026-11-10 09:22:52');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_historico_admin`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_historico_admin`;
CREATE TABLE `tb_historico_admin` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `nome_admin` varchar(100) NOT NULL,
  `acao` varchar(150) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `idx_hist_usuario` (`id_usuario`),
  KEY `idx_hist_data` (`data_hora`),
  KEY `idx_hist_modulo` (`modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `tb_historico_admin` (8 registros)
INSERT INTO `tb_historico_admin` VALUES
('1', '6', 'Miguel Lima', 'Configuração do Sistema', 'sistema', NULL, 'Inicialização do painel administrativo e auditoria', NULL, '2026-11-10 09:22:52'),
('2', '73', 'Verginia dos Santos Silva', 'Verificação de Permissões', 'sistema', NULL, 'Habilitação de acesso administrativo dos 3 administradores oficiais', NULL, '2026-11-10 09:22:52'),
('3', '6', 'Miguel Lima', 'Teste de Auditoria Automatizado', 'sistema', NULL, 'Execução da bateria de testes pelo administrador Miguel Lima', '127.0.0.1', '2026-11-10 09:51:13'),
('4', '6', 'Miguel Lima', 'Teste de Auditoria Automatizado', 'sistema', NULL, 'Execução da bateria de testes pelo administrador Miguel Lima', '127.0.0.1', '2026-11-10 09:55:03'),
('5', '6', 'Miguel Lima', 'Teste de Auditoria Automatizado', 'sistema', NULL, 'Execução da bateria de testes pelo administrador Miguel Lima', '127.0.0.1', '2026-11-10 09:56:39'),
('6', '6', 'Miguel Lima', 'Teste de Auditoria Automatizado', 'sistema', NULL, 'Execução da bateria de testes pelo administrador Miguel Lima', '127.0.0.1', '2026-11-10 11:00:12'),
('7', '6', 'Miguel Lima', 'Teste de Auditoria Automatizado', 'sistema', NULL, 'Execução da bateria de testes pelo administrador Miguel Lima', '127.0.0.1', '2026-11-10 11:02:27'),
('8', '6', 'Miguel Lima', 'Teste de Auditoria Automatizado', 'sistema', NULL, 'Execução da bateria de testes pelo administrador Miguel Lima', '127.0.0.1', '2026-11-10 14:51:02');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_privacidade_aceite`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_privacidade_aceite`;
CREATE TABLE `tb_privacidade_aceite` (
  `id_aceite` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `versao_politica` varchar(20) NOT NULL DEFAULT '2026-11-10',
  `ip_origem` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `data_hora_aceite` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_aceite`),
  KEY `idx_aceite_usuario` (`id_usuario`),
  KEY `idx_aceite_data` (`data_hora_aceite`),
  CONSTRAINT `fk_aceite_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tb_usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dados da tabela `tb_privacidade_aceite` (27 registros)
INSERT INTO `tb_privacidade_aceite` VALUES
('1', '2', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-09-19 12:05:29'),
('2', '4', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-09-19 12:05:29'),
('3', '5', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-09-19 12:05:29'),
('4', '6', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-09-19 12:05:29'),
('5', '7', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-09-19 12:05:29'),
('6', '15', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-10-19 16:27:22'),
('7', '16', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-10-19 16:27:22'),
('8', '20', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-10-19 16:52:47'),
('9', '22', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-10-19 16:54:49'),
('10', '46', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-01 17:47:11'),
('11', '56', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:09'),
('12', '57', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:09'),
('13', '58', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:09'),
('14', '59', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:09'),
('15', '60', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:09'),
('16', '61', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('17', '62', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('18', '63', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('19', '64', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('20', '65', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('21', '66', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('22', '67', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('23', '68', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('24', '69', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('25', '70', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('26', '71', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10'),
('27', '72', '2026-11-10', '127.0.0.1', 'Sistema PetAlert - Registro Inicial', '2026-11-03 09:36:10');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `redefinicao_senha`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `redefinicao_senha`;
CREATE TABLE `redefinicao_senha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `validade` datetime NOT NULL,
  `utilizado` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `redefinicao_senha` (4 registros)
INSERT INTO `redefinicao_senha` VALUES
('1', '73', 'd7bae5229269216c8e68f54630a62c93b9f3884c330bd090b5b3755e0fc3d416', '2026-11-03 20:48:47', '1', '2026-11-03 16:18:47'),
('2', '73', '0758e9399e43735d70989ea299d16697c00576e9a5405e0a17a67a757cd8a34e', '2026-11-03 20:52:37', '0', '2026-11-03 16:22:37'),
('3', '60', '208a1cc5fb56d4e2d205e201cb344943feaa8316784586254c6aa2ee52b2e116', '2026-11-09 17:41:49', '1', '2026-11-09 16:41:49'),
('4', '60', 'bc55dd7345c0269a711728f860d0724479a981b82a4cb0b3c3d0d26b45c7aee9', '2026-11-09 17:52:42', '1', '2026-11-09 16:52:42');

-- ---------------------------------------------------------
-- Estrutura e Dados da tabela `tb_reset_senha`
-- ---------------------------------------------------------
DROP TABLE IF EXISTS `tb_reset_senha`;
CREATE TABLE `tb_reset_senha` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracao` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `idx_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dados da tabela `tb_reset_senha` (6 registros)
INSERT INTO `tb_reset_senha` VALUES
('8', '38', '38a682b72ec9a9623d0929680ea79d058b7c8d04cd95a77836eac05261629730', '2026-11-01 22:48:44', '1', '2026-11-01 18:18:44'),
('9', '38', 'a8e89c93131a72ee1febd098a7ebbc339e6a2bcbeb7e85d714d58ea5b18b29e6', '2026-11-01 22:49:50', '0', '2026-11-01 18:19:50'),
('10', '73', 'c4ef7b5e2f16dd84d96177bbb7c23cd011b63ea8040c6194e034ce0ed4d3b945', '2026-11-03 14:15:55', '1', '2026-11-03 09:45:55'),
('11', '73', '322ac4c4466ab565b5d537ee1554f2d3f674f34b19fa259bc687efc83dd6aaed', '2026-11-03 14:16:37', '1', '2026-11-03 09:46:37'),
('12', '73', 'd7bae5229269216c8e68f54630a62c93b9f3884c330bd090b5b3755e0fc3d416', '2026-11-03 20:48:47', '1', '2026-11-03 16:18:47'),
('13', '73', '0758e9399e43735d70989ea299d16697c00576e9a5405e0a17a67a757cd8a34e', '2026-11-03 20:52:37', '0', '2026-11-03 16:22:37');

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
