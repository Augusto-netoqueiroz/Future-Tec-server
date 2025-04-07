<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Conectando você ao futuro da comunicação com soluções inovadoras para Cloud PBX, disparo de SMS, URA e desenvolvimento de software." />
        <meta name="author" content="FUTURE TEC TELECOM" />
        <title>Future TEC Telecom - Conectando você ao futuro</title>
        <link rel="icon" type="image/x-icon" href="assets/img/future-favicon.ico" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Google fonts-->
        <link rel="preconnect" href="https://fonts.gstatic.com" />
        <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,600;1,600&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,500;0,600;0,700;1,300;1,500;1,600;1,700&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;1,400&amp;display=swap" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
        <!-- Estilos customizados para animação e moldura moderna -->
        <style>
            .animated-image {
                animation: float 3s ease-in-out infinite;
                border: 5px solid #ffffff;
                border-radius: 10px;
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            }
            @keyframes float {
                0% {
                    transform: translateY(0px);
                }
                50% {
                    transform: translateY(-20px);
                }
                100% {
                    transform: translateY(0px);
                }
            }
        </style>
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" id="mainNav">
            <div class="container px-5">
                <a class="navbar-brand fw-bold" href="#page-top">FUTURE TEC TELECOM</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" 
                        aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="bi-list"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto me-4 my-3 my-lg-0">
                        <li class="nav-item"><a class="nav-link me-lg-3" href="#features">Serviços</a></li>
                        <li class="nav-item"><a class="nav-link me-lg-3" href="#plans">Planos</a></li>
                    </ul>
                    <button class="btn btn-primary rounded-pill px-3 mb-2 mb-lg-0" onclick="window.location.href='{{ route('login') }}'">
                        <span class="d-flex align-items-center">
                            <i class="bi-chat-text-fill me-2"></i>
                            <span class="small">Área do Cliente</span>
                        </span>
                    </button>
                </div>
            </div>
        </nav>
        <!-- Mashead header-->
        <header class="masthead">
            <div class="container px-5">
                <div class="row gx-5 align-items-center">
                    <!-- Texto Inicial -->
                    <div class="col-md-6">
                        <div class="mb-5 text-start">
                            <h1 class="display-1 lh-1 mb-3">Conecte-se ao futuro com a FUTURE TEC TELECOM</h1>
                            <p class="lead fw-normal text-muted mb-5">
                                Potencialize a comunicação da sua empresa com nossas soluções inovadoras: Cloud PBX, disparo de SMS, URA inteligente e desenvolvimento de software personalizado.
                            </p>
                        </div>
                    </div>
                    <!-- Imagem ao lado do Texto com animação e moldura moderna -->
                    <div class="col-md-6">
                        <img src="assets/img/16341.jpg" class="img-fluid animated-image" alt="Imagem representativa de telecomunicações">
                    </div>
                </div>
            </div>
        </header>
        <!-- Resto do código permanece inalterado -->
        <!-- Quote/testimonial aside-->
        <!--<aside class="text-center bg-gradient-primary-to-secondary">
            <div class="container px-5">
                <div class="row gx-5 justify-content-center">
                    <div class="col-xl-8">
                        <div class="h2 fs-1 text-white mb-4">
                            "A FUTURE TEC TELECOM transformou nossa comunicação, oferecendo soluções confiáveis e um atendimento excepcional."
                        </div>
                        <img src="assets/img/future-testimonial.svg" alt="Testemunho de Cliente" style="height: 3rem" />
                    </div>
                </div>
            </div>
        </aside>-->
        <!-- Serviços section-->
         <!-- Serviços section-->
        <section id="features">
            <div class="container px-5">
                <div class="row gx-5 align-items-center justify-content-center">
                    <div class="col-lg-8 order-lg-1 mb-5 mb-lg-0 mx-auto">
                        <div class="container-fluid px-5">
                            <div class="row gx-5">
                                <!-- Solução Cloud PBX -->
                                <div class="col-md-6 mb-5">
                                    <div class="text-center">
                                        <i class="bi-phone icon-feature text-gradient d-block mb-3"></i>
                                        <h3 class="font-alt">Soluções Cloud PBX</h3>
                                        <p class="text-muted mb-0">Central telefônica virtual, escalável e flexível para sua empresa.</p>
                                    </div>
                                </div>
                                <!-- Disparo de SMS -->
                                <div class="col-md-6 mb-5">
                                    <div class="text-center">
                                        <i class="bi-chat icon-feature text-gradient d-block mb-3"></i>
                                        <h3 class="font-alt">Disparo de SMS</h3>
                                        <p class="text-muted mb-0">Envie mensagens em massa de forma eficiente e segura.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- URA Inteligente -->
                                <div class="col-md-6 mb-5 mb-md-0">
                                    <div class="text-center">
                                        <i class="bi-megaphone icon-feature text-gradient d-block mb-3"></i>
                                        <h3 class="font-alt">URA Inteligente</h3>
                                        <p class="text-muted mb-0">Sistemas de atendimento automatizado para melhorar o suporte ao cliente.</p>
                                    </div>
                                </div>
                                <!-- Desenvolvimento de Software -->
                                <div class="col-md-6">
                                    <div class="text-center">
                                        <i class="bi-code-slash icon-feature text-gradient d-block mb-3"></i>
                                        <h3 class="font-alt">Desenvolvimento de Software</h3>
                                        <p class="text-muted mb-0">Soluções personalizadas para otimizar processos e impulsionar seu negócio.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Elemento removido anteriormente, sem substituição nesta seção -->
                </div>
            </div>
        </section>

        <!-- Seção de Planos-->
        <section id="plans" class="bg-light">
            <div class="container px-5">
                <div class="text-center mb-5">
                    <h2 class="display-4">Nossos Planos</h2>
                    <p class="lead text-muted">
                        Escolha o plano ideal para potencializar sua comunicação. Na FUTURE TEC TELECOM, você encontra soluções inovadoras para Cloud PBX, disparo de SMS, URA e desenvolvimento de software.
                    </p>
                </div>
                <div class="row">
                    <!-- Plano Básico -->
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <h4 class="my-0 fw-normal">Plano Básico</h4>
                            </div>
                            <div class="card-body">
                                <h1 class="card-title pricing-card-title">R$ 300,00<small class="text-muted">/mês</small></h1>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>PBX em Cloud Completo</li>
                                    <li>Envio de até 500 SMS</li>
                                    <li>URA Reversa e Receptiva</li>
                                    <li>Até 10 usuários e 10 Ramais</li>
                                </ul>
                                <a href="https://wa.me/61981503910?text=Ol%C3%A1%20estou%20interessado%20nos%20planos" 
                                   class="w-100 btn btn-lg btn-outline-primary">Assine já</a>
                            </div>
                        </div>
                    </div>
                    <!-- Plano Avançado -->
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <h4 class="my-0 fw-normal">Plano Avançado</h4>
                            </div>
                            <div class="card-body">
                                <h1 class="card-title pricing-card-title">R$ 500,00<small class="text-muted">/mês</small></h1>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>PBX em Cloud Completo</li>
                                    <li>Envio de até 1000 SMS</li>
                                    <li>URA Reversa e Receptiva</li>
                                    <li>Até 25 usuários e 25 Ramais</li>
                                </ul>
                                <a href="https://wa.me/61981503910?text=Ol%C3%A1%20estou%20interessado%20nos%20planos" 
                                   class="w-100 btn btn-lg btn-primary">Assine já</a>
                            </div>
                        </div>
                    </div>
                    <!-- Plano Premium -->
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header">
                                <h4 class="my-0 fw-normal">Plano Premium</h4>
                            </div>
                            <div class="card-body">
                                <h1 class="card-title pricing-card-title">R$ 1000,00<small class="text-muted">/mês</small></h1>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li>PBX em Cloud Completo</li>
                                    <li>Envio de até 3000 SMS</li>
                                    <li>URA Reversa e Receptiva</li>
                                    <li>Até 50 usuários e 50 Ramais</li>
                                    <li>Integrações com ERP Hubsoft | OpaSuite| SGP</li>
                                </ul>
                                <a href="https://wa.me/61981503910?text=Ol%C3%A1%20estou%20interessado%20nos%20planos" 
                                   class="w-100 btn btn-lg btn-primary">Assine já</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Basic features section-->
        <section class="bg-light">
            <div class="container px-5">
                <div class="row gx-5 align-items-center justify-content-center justify-content-lg-between">
                    <div class="col-12 col-lg-5">
                        <h2 class="display-4 lh-1 mb-4">Bem-vindo à FUTURE TEC TELECOM</h2>
                        <p class="lead fw-normal text-muted mb-5 mb-lg-0">
                            Oferecemos soluções inovadoras e personalizadas para otimizar a comunicação e os processos da sua empresa. Conte com nossa expertise em Cloud PBX, SMS em massa, URA inteligente e desenvolvimento de software para impulsionar o seu negócio.
                        </p>
                    </div>
                    <div class="col-sm-8 col-md-6">
                        <div class="px-5 px-sm-0">
                            <!-- Imagem com temática de telecomunicações -->
                            <!--<img class="img-fluid rounded-circle" src="https://source.unsplash.com/900x900/?telecom,cloud" alt="Soluções em Telecomunicações" /> -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Call to action section-->
        <section class="cta">
            <div class="cta-content">
                <div class="container px-5">
                    <h2 class="text-white display-1 lh-1 mb-4">
                        Não espere.
                        <br />
                        Potencialize sua comunicação.
                    </h2>
                    <a class="btn btn-outline-light py-3 px-4 rounded-pill" href="#plans">Assine já</a>
                </div>
            </div>
        </section>
        <!-- App badge section-->
        <!--<section class="bg-gradient-primary-to-secondary" id="download">
            <div class="container px-5">
                <h2 class="text-center text-white font-alt mb-4">Baixe nosso app!</h2>
                <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center">
                    <a class="me-lg-3 mb-4 mb-lg-0" href="#!">
                        <img class="app-badge" src="assets/img/google-play-badge.svg" alt="Baixe no Google Play" />
                    </a>
                    <a href="#!">
                        <img class="app-badge" src="assets/img/app-store-badge.svg" alt="Baixe na App Store" />
                    </a>
                </div>
            </div>
        </section>-->
        <!-- Footer-->
        <footer class="bg-black text-center py-5">
            <div class="container px-5">
                <div class="text-white-50 small">
                    <div class="mb-2">&copy; FUTURE TEC TELECOM 2025. Todos os direitos reservados.</div>
                    <a href="#!">Política de Privacidade</a>
                    <span class="mx-1">&middot;</span>
                    <a href="#!">Termos de Uso</a>
                    <span class="mx-1">&middot;</span>
                    <a href="#!">FAQ</a>
                </div>
            </div>
        </footer>
        <!-- Feedback Modal-->
        <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary-to-secondary p-4">
                        <h5 class="modal-title font-alt text-white" id="feedbackModalLabel">Fale Conosco</h5>
                        <button class="btn-close btn-close-white" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body border-0 p-4">
                        <form id="contactForm" data-sb-form-api-token="API_TOKEN">
                            <!-- Nome input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="name" type="text" placeholder="Digite seu nome..." data-sb-validations="required" />
                                <label for="name">Nome Completo</label>
                                <div class="invalid-feedback" data-sb-feedback="name:required">O nome é obrigatório.</div>
                            </div>
                            <!-- Email input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" type="email" placeholder="seuemail@exemplo.com" data-sb-validations="required,email" />
                                <label for="email">Endereço de Email</label>
                                <div class="invalid-feedback" data-sb-feedback="email:required">O email é obrigatório.</div>
                                <div class="invalid-feedback" data-sb-feedback="email:email">Email não é válido.</div>
                            </div>
                            <!-- Telefone input-->
                            <div class="form-floating mb-3">
                                <input class="form-control" id="phone" type="tel" placeholder="(11) 98765-4321" data-sb-validations="required" />
                                <label for="phone">Telefone</label>
                                <div class="invalid-feedback" data-sb-feedback="phone:required">O telefone é obrigatório.</div>
                            </div>
                            <!-- Mensagem input-->
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="message" placeholder="Digite sua mensagem..." style="height: 10rem" data-sb-validations="required"></textarea>
                                <label for="message">Mensagem</label>
                                <div class="invalid-feedback" data-sb-feedback="message:required">A mensagem é obrigatória.</div>
                            </div>
                            <!-- Submit success message-->
                            <div class="d-none" id="submitSuccessMessage">
                                <div class="text-center mb-3">
                                    <div class="fw-bolder">Formulário enviado com sucesso!</div>
                                    Para ativar este formulário, inscreva-se em
                                    <br />
                                    <a href="https://startbootstrap.com/solution/contact-forms">https://startbootstrap.com/solution/contact-forms</a>
                                </div>
                            </div>
                            <!-- Submit error message-->
                            <div class="d-none" id="submitErrorMessage">
                                <div class="text-center text-danger mb-3">Erro ao enviar mensagem!</div>
                            </div>
                            <!-- Botão de Envio-->
                            <div class="d-grid">
                                <button class="btn btn-primary rounded-pill btn-lg disabled" id="submitButton" type="submit">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('js/scripts.js') }}"></script>
    </body>
</html>
