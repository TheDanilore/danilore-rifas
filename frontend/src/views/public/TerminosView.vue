<template>
    <div class="terminos-page">
        <!-- Hero Section -->
        <section class="hero hero-enhanced hero-content-page">
            <div class="hero-bg"></div>
            <div class="container">
                <div class="hero-content">
                    <div class="hero-icons">
                        <i class="fas fa-file-contract contract-icon"></i>
                        <h1 class="hero-title">Términos y Condiciones</h1>
                        <i class="fas fa-file-contract contract-icon"></i>
                    </div>
                    <p class="hero-subtitle">
                        Conoce nuestras políticas y condiciones de uso
                    </p>
                    <div class="last-update">
                        <i class="fas fa-calendar-alt"></i>
                        Última actualización: 23 de Julio 2025
                    </div>
                </div>
            </div>
        </section>

        <!-- Navegación Rápida -->
        <section class="nav-section">
            <div class="container">
                <div class="nav-quick">
                    <h3>Navegación Rápida</h3>
                    <div class="nav-links">
                        <a v-for="section in sections" :key="section.id" :href="`#${section.id}`" class="nav-link"
                            @click="scrollToSection(section.id)">
                            <i :class="section.icon"></i>
                            {{ section.title }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contenido de Términos -->
        <section class="content-section">
            <div class="container">
                <div class="content-grid">

                    <!-- Índice lateral -->
                    <aside class="sidebar">
                        <div class="sidebar-sticky">
                            <h4>Contenido</h4>
                            <nav class="sidebar-nav">
                                <a v-for="section in sections" :key="section.id" :href="`#${section.id}`"
                                    class="sidebar-link" :class="{ 'active': activeSection === section.id }"
                                    @click="scrollToSection(section.id)">
                                    {{ section.title }}
                                </a>
                            </nav>
                        </div>
                    </aside>

                    <!-- Contenido principal -->
                    <main class="main-content">

                        <!-- 1. Información General -->
                        <article :id="sections[0].id" class="content-article">
                            <h2>
                                <i class="fas fa-info-circle"></i>
                                {{ sections[0].title }}
                            </h2>
                            <div class="article-content">
                                <p>
                                    Bienvenido a <strong>Danilore Rifas</strong>. Estos términos y condiciones regulan
                                    el uso de nuestros servicios de rifas en línea. Al participar en cualquiera de
                                    nuestras rifas, aceptas automáticamente estos términos.
                                </p>
                                <div class="info-box">
                                    <h4>Datos de la Empresa</h4>
                                    <ul>
                                        <li><strong>Nombre:</strong> Danilore Rifas</li>
                                        <li><strong>Tipo:</strong> Plataforma de rifas en línea</li>
                                        <li><strong>País:</strong> Perú</li>
                                        <li><strong>Contacto:</strong> info@danilorerifas.com</li>
                                    </ul>
                                </div>
                                <p>
                                    Nos reservamos el derecho de modificar estos términos en cualquier momento. Los
                                    cambios serán comunicados a través de nuestro sitio web y redes sociales oficiales.
                                </p>
                            </div>
                        </article>

                        <!-- 2. Registro y Participación -->
                        <article :id="sections[1].id" class="content-article">
                            <h2>
                                <i class="fas fa-user-plus"></i>
                                {{ sections[1].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Requisitos para Participar</h4>
                                <ul>
                                    <li>Ser mayor de 13 años</li>
                                    <li>Residir en territorio peruano</li>
                                    <li>Proporcionar información personal veraz y actualizada</li>
                                    <li>Tener un método de pago válido (Yape o Plin)</li>
                                </ul>

                                <h4>Proceso de Registro</h4>
                                <ol>
                                    <li>Crear cuenta con email válido</li>
                                    <li>Verificar identidad mediante documento de identidad</li>
                                    <li>Aceptar términos y condiciones</li>
                                </ol>

                                <div class="warning-box">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Importante:</strong> Está prohibido crear múltiples cuentas. Detectamos y
                                    penalizamos esta práctica con la suspensión permanente de todas las cuentas
                                    relacionadas.
                                </div>
                            </div>
                        </article>

                        <!-- 3. Sistema de Rifas -->
                        <article :id="sections[2].id" class="content-article">
                            <h2>
                                <i class="fas fa-cogs"></i>
                                {{ sections[2].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Sistema de Desbloqueo Progresivo</h4>
                                <p>
                                    Operamos con un sistema único de <strong>desbloqueo progresivo</strong>:
                                </p>
                                <ul>
                                    <li>Solo una rifa está disponible para participar</li>
                                    <li>Al completarse una rifa, automáticamente se habilita la siguiente. Puede ser en
                                        la misma fecha o en la siguiente fecha</li>
                                    <li>Las rifas bloqueadas se muestran como vista previa</li>
                                    <li>No se puede participar en rifas futuras hasta que se complete la cantidad de
                                        tickets máximo de la actual</li>
                                </ul>

                                <h4>Estados de las Rifas</h4>
                                <div class="status-grid">
                                    <div class="status-card active">
                                        <div class="status-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                        <h5>Activa</h5>
                                        <p>Disponible para participar. Se pueden comprar tickets.</p>
                                    </div>
                                    <div class="status-card next">
                                        <div class="status-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <h5>Próxima</h5>
                                        <p>Se habilitará al completarse la rifa actual.</p>
                                    </div>
                                    <div class="status-card locked">
                                        <div class="status-icon">
                                            <i class="fas fa-lock"></i>
                                        </div>
                                        <h5>Bloqueada</h5>
                                        <p>Solo visible como vista previa.</p>
                                    </div>
                                </div>

                                <h4>Condiciones de Finalización</h4>
                                <p>
                                    Una rifa se considera completada cuando:
                                </p>
                                <ul>
                                    <li>Se alcanza el <strong>número mínimo de participantes</strong> establecido</li>
                                </ul>
                            </div>
                        </article>

                        <!-- 4. Pagos y Reembolsos -->
                        <article :id="sections[3].id" class="content-article">
                            <h2>
                                <i class="fas fa-credit-card"></i>
                                {{ sections[3].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Métodos de Pago Aceptados</h4>
                                <div class="payment-methods">
                                    <div class="payment-card">
                                        <img src="https://logosenvector.com/logo/img/yape-37283.png" alt="Yape">
                                        <div>
                                            <h5>Yape</h5>
                                            <p>Pago instantáneo mediante código QR</p>
                                        </div>
                                    </div>
                                    <div class="payment-card">
                                        <img src="https://images.seeklogo.com/logo-png/38/1/plin-logo-png_seeklogo-386806.png"
                                            alt="Plin">
                                        <div>
                                            <h5>Plin</h5>
                                            <p>Transferencia entre bancos</p>
                                        </div>
                                    </div>
                                </div>

                                <h4>Política de Precios</h4>
                                <ul>
                                    <li><strong>Precio por ticket:</strong> S/2.00 (dos soles)</li>
                                    <li><strong>Sin comisiones adicionales:</strong> El precio mostrado es el precio
                                        final</li>
                                    <li><strong>Confirmación instantánea:</strong> El pago se valida automáticamente
                                    </li>
                                </ul>

                                <h4>Política de Reembolsos</h4>
                                <div class="refund-policy">
                                    <h5>✅ Reembolso Completo (100%)</h5>
                                    <ul>
                                        <li>Si la rifa es cancelada por decisión de la empresa</li>
                                        <li>Si no se alcanza el mínimo de participantes en el tiempo establecido</li>
                                        <li>Si se detectan irregularidades en el sorteo</li>
                                    </ul>

                                    <h5>❌ No Hay Reembolso</h5>
                                    <ul>
                                        <li>Una vez que la rifa alcanza el mínimo de participantes</li>
                                        <li>Después de realizado el sorteo</li>
                                        <li>Por arrepentimiento o cambio de opinión del participante</li>
                                    </ul>
                                </div>

                                <div class="info-box">
                                    <h4>Proceso de Reembolso</h4>
                                    <p>Los reembolsos se procesan automáticamente y se devuelve el dinero al mismo
                                        método de pago utilizado en un plazo máximo de 5 días hábiles.</p>
                                </div>
                            </div>
                        </article>

                        <!-- 5. Sorteos y Transparencia -->
                        <article :id="sections[4].id" class="content-article">
                            <h2>
                                <i class="fas fa-random"></i>
                                {{ sections[4].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Metodología de Sorteo</h4>
                                <p>
                                    Garantizamos la transparencia total en todos nuestros sorteos:
                                </p>
                                <ul>
                                    <li><strong>Transmisión en vivo:</strong> Todos los sorteos son transmitidos por
                                        nuestras redes sociales</li>
                                    <li><strong>Sistema aleatorio:</strong> Utilizamos generadores de números aleatorios
                                        verificables</li>
                                    <li><strong>Grabación completa:</strong> Todas las transmisiones quedan registradas
                                    </li>
                                    <li><strong>Participación del público:</strong> Los espectadores pueden verificar el
                                        proceso</li>
                                </ul>

                                <h4>Cronograma de Sorteos</h4>
                                <div class="schedule-info">
                                    <div class="schedule-item">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <h5>Según la fecha de la rifa</h5>
                                            <p>Se realiza en la fecha programada al alcanzar el mínimo de participantes
                                            </p>
                                        </div>
                                    </div>
                                    <div class="schedule-item">
                                        <i class="fas fa-clock"></i>
                                        <div>
                                            <h5>Horarios estimado</h5>
                                            <p>Sábados y Domingos: 8:00 PM</p>
                                        </div>
                                    </div>
                                </div>

                                <h4>Validación de Resultados</h4>
                                <p>
                                    Después de cada sorteo:
                                </p>
                                <ol>
                                    <li>Se anuncia el número ganador en vivo</li>
                                    <li>Se publica el resultado en todas las redes sociales</li>
                                    <li>Se contacta directamente al ganador</li>
                                    <li>Se actualiza la página de ganadores</li>
                                </ol>
                                <div class="info-box">
                                    <h4>Importante</h4>
                                    <p>Los ganadores tienen 7 días para reclamar su premio. Pasado este plazo, el
                                        premio se le entrega al segundo lugar. Si no se contacta con el segundo lugar,
                                        el premio se entrega al tercer lugar. Si no se contacta con el tercer lugar, el
                                        premio se declara desierto.</p>
                                </div>
                            </div>
                        </article>

                        <!-- 6. Premios y Entregas -->
                        <article :id="sections[5].id" class="content-article">
                            <h2>
                                <i class="fas fa-gift"></i>
                                {{ sections[5].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Características de los Premios</h4>
                                <ul>
                                    <li><strong>100% Originales:</strong> Todos los productos son auténticos</li>
                                    <li><strong>Garantía del fabricante:</strong> Incluyen garantía oficial</li>
                                    <li><strong>Estado nuevo:</strong> Los productos están completamente nuevos y
                                        sellados</li>
                                    <li><strong>Verificación de autenticidad:</strong> Cada premio incluye certificado
                                    </li>
                                </ul>

                                <h4>Proceso de Entrega</h4>
                                <div class="delivery-process">
                                    <div class="process-step">
                                        <div class="step-number">1</div>
                                        <div class="step-content">
                                            <h5>Contacto Inmediato</h5>
                                            <p>Te contactamos dentro de las 24 horas siguientes al sorteo</p>
                                        </div>
                                    </div>
                                    <div class="process-step">
                                        <div class="step-number">2</div>
                                        <div class="step-content">
                                            <h5>Verificación de Datos</h5>
                                            <p>Confirmamos tu identidad y dirección de entrega</p>
                                        </div>
                                    </div>
                                    <div class="process-step">
                                        <div class="step-number">3</div>
                                        <div class="step-content">
                                            <h5>Envío Gratuito</h5>
                                            <p>Coordenamos la entrega sin costo adicional para ti</p>
                                        </div>
                                    </div>
                                    <div class="process-step">
                                        <div class="step-number">4</div>
                                        <div class="step-content">
                                            <h5>Recepción del Premio</h5>
                                            <p>Recibes tu premio con certificado de autenticidad</p>
                                        </div>
                                    </div>
                                </div>

                                <h4>Cobertura de Entrega</h4>
                                <ul>
                                    <li><strong>Provincias:</strong> 3-7 días hábiles</li>
                                    <li><strong>Zonas remotas:</strong> Hasta 10 días hábiles</li>
                                </ul>

                                <div class="warning-box">
                                    <i class="fas fa-clock"></i>
                                    <strong>Plazo para reclamar:</strong> Los ganadores tienen 7 días para reclamar su
                                    premio. Pasado este plazo, el
                                    premio se le entrega al segundo lugar. Si no se contacta con el segundo lugar,
                                    el premio se entrega al tercer lugar. Si no se contacta con el tercer lugar, el
                                    premio se declara desierto.
                                </div>
                            </div>
                        </article>

                        <!-- 7. Privacidad y Datos -->
                        <article :id="sections[6].id" class="content-article">
                            <h2>
                                <i class="fas fa-shield-alt"></i>
                                {{ sections[6].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Recopilación de Datos</h4>
                                <p>
                                    Recopilamos únicamente la información necesaria para:
                                </p>
                                <ul>
                                    <li>Procesar tu participación en las rifas</li>
                                    <li>Contactarte en caso de resultar ganador</li>
                                    <li>Cumplir con obligaciones legales y fiscales</li>
                                    <li>Mejorar nuestros servicios</li>
                                </ul>

                                <h4>Datos que Recopilamos</h4>
                                <div class="data-types">
                                    <div class="data-type">
                                        <h5>Datos Personales</h5>
                                        <ul>
                                            <li>Nombre completo</li>
                                            <li>Documento de identidad</li>
                                            <li>Fecha de nacimiento</li>
                                            <li>Teléfono y email</li>
                                            <li>Dirección de residencia</li>
                                        </ul>
                                    </div>
                                    <div class="data-type">
                                        <h5>Datos de Participación</h5>
                                        <ul>
                                            <li>Historial de rifas</li>
                                            <li>Tickets comprados</li>
                                            <li>Métodos de pago</li>
                                            <li>Premios ganados</li>
                                        </ul>
                                    </div>
                                </div>

                                <h4>Uso de la Información</h4>
                                <ul>
                                    <li><strong>No vendemos</strong> tus datos a terceros</li>
                                    <li><strong>No enviamos</strong> spam ni publicidad no solicitada</li>
                                    <li><strong>Protegemos</strong> tu información con medidas de seguridad robustas
                                    </li>
                                    <li><strong>Cumplimos</strong> con la Ley de Protección de Datos Personales del Perú
                                    </li>
                                </ul>

                                <h4>Tus Derechos</h4>
                                <p>
                                    Como titular de datos personales, tienes derecho a:
                                </p>
                                <ul>
                                    <li>Acceder a tu información personal</li>
                                    <li>Rectificar datos incorrectos</li>
                                    <li>Cancelar tu cuenta y datos</li>
                                    <li>Oponerte al tratamiento de tus datos</li>
                                </ul>
                            </div>
                        </article>

                        <!-- 8. Responsabilidades -->
                        <article :id="sections[7].id" class="content-article">
                            <h2>
                                <i class="fas fa-balance-scale"></i>
                                {{ sections[7].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Nuestras Responsabilidades</h4>
                                <ul>
                                    <li>Garantizar la transparencia en todos los sorteos</li>
                                    <li>Entregar los premios a los ganadores legítimos</li>
                                    <li>Proteger la información personal de los usuarios</li>
                                    <li>Mantener la plataforma funcionando correctamente</li>
                                    <li>Procesar reembolsos cuando corresponda</li>
                                </ul>

                                <h4>Responsabilidades del Usuario</h4>
                                <ul>
                                    <li>Proporcionar información veraz y actualizada</li>
                                    <li>Cumplir con los términos y condiciones</li>
                                    <li>No crear múltiples cuentas</li>
                                    <li>No interferir con el funcionamiento de la plataforma</li>
                                    <li>Reclamar los premios en el plazo establecido</li>
                                </ul>

                                <h4>Limitaciones de Responsabilidad</h4>
                                <div class="limitations">
                                    <p>
                                        <strong>Danilore Rifas</strong> no se hace responsable por:
                                    </p>
                                    <ul>
                                        <li>Problemas técnicos del dispositivo del usuario</li>
                                        <li>Fallas en los métodos de pago externos (Yape, Plin)</li>
                                        <li>Información incorrecta proporcionada por el usuario</li>
                                        <li>Premios no reclamados en el plazo establecido</li>
                                        <li>Daños indirectos o consecuenciales</li>
                                    </ul>
                                </div>
                            </div>
                        </article>

                        <!-- 9. Prohibiciones -->
                        <article :id="sections[8].id" class="content-article">
                            <h2>
                                <i class="fas fa-ban"></i>
                                {{ sections[8].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Conductas Prohibidas</h4>
                                <div class="prohibited-list">
                                    <div class="prohibited-item">
                                        <i class="fas fa-user-times"></i>
                                        <div>
                                            <h5>Múltiples Cuentas</h5>
                                            <p>Crear más de una cuenta por persona está estrictamente prohibido</p>
                                        </div>
                                    </div>
                                    <div class="prohibited-item">
                                        <i class="fas fa-robot"></i>
                                        <div>
                                            <h5>Automatización</h5>
                                            <p>Usar bots o scripts para automatizar la compra de tickets</p>
                                        </div>
                                    </div>
                                    <div class="prohibited-item">
                                        <i class="fas fa-credit-card"></i>
                                        <div>
                                            <h5>Fraude de Pagos</h5>
                                            <p>Realizar pagos fraudulentos o usar métodos de pago de terceros</p>
                                        </div>
                                    </div>
                                    <div class="prohibited-item">
                                        <i class="fas fa-share-alt"></i>
                                        <div>
                                            <h5>Compartir Cuentas</h5>
                                            <p>Compartir credenciales de acceso con otras personas</p>
                                        </div>
                                    </div>
                                </div>

                                <h4>Consecuencias</h4>
                                <p>
                                    Las violaciones a estos términos pueden resultar en:
                                </p>
                                <ul>
                                    <li><strong>Advertencia formal</strong> por primera infracción</li>
                                    <li><strong>Suspensión temporal</strong> por infracciones repetidas</li>
                                    <li><strong>Suspensión permanente</strong> por infracciones graves</li>
                                    <li><strong>Pérdida de premios</strong> ganados de manera fraudulenta</li>
                                </ul>
                            </div>
                        </article>

                        <!-- 10. Resolución de Conflictos -->
                        <article :id="sections[9].id" class="content-article">
                            <h2>
                                <i class="fas fa-gavel"></i>
                                {{ sections[9].title }}
                            </h2>
                            <div class="article-content">
                                <h4>Proceso de Reclamos</h4>
                                <div class="claims-process">
                                    <div class="claim-step">
                                        <div class="claim-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div>
                                            <h5>1. Contacto Directo</h5>
                                            <p>Envía tu reclamo a <strong>reclamos@danilorerifas.com</strong></p>
                                        </div>
                                    </div>
                                    <div class="claim-step">
                                        <div class="claim-icon">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <div>
                                            <h5>2. Investigación</h5>
                                            <p>Revisamos tu caso en un plazo máximo de 5 días hábiles</p>
                                        </div>
                                    </div>
                                    <div class="claim-step">
                                        <div class="claim-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div>
                                            <h5>3. Resolución</h5>
                                            <p>Te comunicamos la decisión y las acciones a tomar</p>
                                        </div>
                                    </div>
                                </div>

                                <h4>Jurisdicción</h4>
                                <p>
                                    Estos términos se rigen por las leyes de la República del Perú. Cualquier
                                    controversia será resuelta por los tribunales competentes de Lima, Perú.
                                </p>

                                <h4>Mediación</h4>
                                <p>
                                    Antes de iniciar acciones legales, las partes se comprometen a intentar resolver las
                                    controversias mediante mediación en el Centro de Mediación de la Cámara de Comercio
                                    de Lima.
                                </p>
                            </div>
                        </article>

                    </main>
                </div>
            </div>
        </section>

        <!-- Contacto -->
        <section class="contact-section">
            <div class="container">
                <div class="contact-content">
                    <h2>¿Tienes Dudas sobre nuestros Términos?</h2>
                    <p>Estamos aquí para ayudarte. Contáctanos si necesitas aclaraciones.</p>
                    <div class="contact-methods">
                        <!-- <div class="contact-method">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>info@danilorerifas.com</p>
                            </div>
                        </div> -->
                        <div class="contact-method">
                            <i class="fab fa-whatsapp"></i>
                            <div>
                                <h4>WhatsApp</h4>
                                <p>+51 936 081 881</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'

export default {
    name: 'TerminosCondiciones',
    setup() {
        const activeSection = ref('')

        const sections = ref([
            { id: 'info-general', title: 'Información General', icon: 'fas fa-info-circle' },
            { id: 'registro', title: 'Registro y Participación', icon: 'fas fa-user-plus' },
            { id: 'sistema-rifas', title: 'Sistema de Rifas', icon: 'fas fa-cogs' },
            { id: 'pagos', title: 'Pagos y Reembolsos', icon: 'fas fa-credit-card' },
            { id: 'sorteos', title: 'Sorteos y Transparencia', icon: 'fas fa-random' },
            { id: 'premios', title: 'Premios y Entregas', icon: 'fas fa-gift' },
            { id: 'privacidad', title: 'Privacidad y Datos', icon: 'fas fa-shield-alt' },
            { id: 'responsabilidades', title: 'Responsabilidades', icon: 'fas fa-balance-scale' },
            { id: 'prohibiciones', title: 'Prohibiciones', icon: 'fas fa-ban' },
            { id: 'conflictos', title: 'Resolución de Conflictos', icon: 'fas fa-gavel' }
        ])

        const scrollToSection = (sectionId) => {
            const element = document.getElementById(sectionId)
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' })
            }
        }

        const handleScroll = () => {
            const scrollPosition = window.scrollY + 200

            for (const section of sections.value) {
                const element = document.getElementById(section.id)
                if (element) {
                    const offsetTop = element.offsetTop
                    const offsetBottom = offsetTop + element.offsetHeight

                    if (scrollPosition >= offsetTop && scrollPosition < offsetBottom) {
                        activeSection.value = section.id
                        break
                    }
                }
            }
        }

        onMounted(() => {
            window.addEventListener('scroll', handleScroll)
            handleScroll() // Initial check
        })

        onUnmounted(() => {
            window.removeEventListener('scroll', handleScroll)
        })

        return {
            sections,
            activeSection,
            scrollToSection
        }
    }
}
</script>

<style scoped>
.terminos-page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--gray-50);
}

/* Hero Section */
.hero {
    position: relative;
    padding: 4rem 0;
    text-align: center;
    background: linear-gradient(135deg, var(--primary-indigo) 0%, var(--primary-blue) 50%, var(--primary-purple) 100%);
}

.hero-bg {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.1);
}

.hero-content {
    position: relative;
    color: white;
}

.hero-icons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.contract-icon {
    font-size: 2.5rem;
    color: #60a5fa;
}

.hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 700;
    margin: 0;
}

.hero-subtitle {
    font-size: 1.25rem;
    margin: 0 0 1rem 0;
    opacity: 0.9;
}

.last-update[data-v-57ade856] {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    opacity: 0.8;
    color: var(--gray-800);
}

/* Navegación Rápida */
.nav-section {
    padding: 2rem 0;
    background: var(--white);
    border-bottom: 1px solid var(--gray-200);
}

.nav-quick h3 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: var(--gray-800);
}

.nav-links {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1rem;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--gray-100);
    border-radius: var(--border-radius-full);
    text-decoration: none;
    color: var(--gray-700);
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: var(--primary-purple);
    color: white;
    transform: translateY(-2px);
}

/* Content Section */
.content-section {
    padding: 3rem 0;
}

.content-grid {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 3rem;
}

/* Sidebar */
.sidebar {
    position: relative;
}

.sidebar-sticky {
    position: sticky;
    top: 2rem;
    background: var(--white);
    padding: 2rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
}

.sidebar h4 {
    margin-bottom: 1rem;
    color: var(--gray-800);
    font-size: 1.125rem;
}

.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.sidebar-link {
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius-md);
    text-decoration: none;
    color: var(--gray-600);
    font-size: 0.875rem;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.sidebar-link:hover {
    background: var(--gray-100);
    color: var(--gray-800);
}

.sidebar-link.active {
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
    color: white;
    border-left-color: var(--primary-purple);
}

/* Main Content */
.main-content {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.content-article {
    padding: 3rem;
    border-bottom: 1px solid var(--gray-200);
}

.content-article:last-child {
    border-bottom: none;
}

.content-article h2 {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--gray-200);
}

.content-article h2 i {
    color: var(--primary-purple);
}

.article-content h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    margin: 2rem 0 1rem 0;
}

.article-content h5 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--gray-700);
    margin: 1.5rem 0 0.75rem 0;
}

.article-content p {
    line-height: 1.7;
    color: var(--gray-600);
    margin-bottom: 1.5rem;
}

.article-content ul,
.article-content ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.article-content li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
    color: var(--gray-600);
}

/* Info Boxes */
.info-box,
.warning-box {
    padding: 1.5rem;
    border-radius: var(--border-radius-md);
    margin: 2rem 0;
}

.info-box {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    border-left: 4px solid var(--primary-blue);
}

.warning-box {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-left: 4px solid #f59e0b;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.warning-box i {
    color: #f59e0b;
    font-size: 1.25rem;
    margin-top: 0.25rem;
}

/* Status Grid */
.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}

.status-card {
    padding: 1.5rem;
    border-radius: var(--border-radius-md);
    text-align: center;
    color: white;
}

.status-card.active {
    background: linear-gradient(135deg, var(--success-green), #10b981);
}

.status-card.next {
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
}

.status-card.locked {
    background: linear-gradient(135deg, var(--gray-500), var(--gray-600));
}

.status-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.status-card h5 {
    font-size: 1.125rem;
    margin-bottom: 0.5rem;
    color: white;
}

.status-card p {
    font-size: 0.875rem;
    margin: 0;
    opacity: 0.9;
    color: white;
}

/* Payment Methods */
.payment-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}

.payment-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: var(--border-radius-md);
}

.payment-card img {
    width: 3rem;
    height: 3rem;
    object-fit: contain;
}

.payment-card h5 {
    margin: 0 0 0.5rem 0;
    color: var(--gray-800);
}

.payment-card p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

/* Refund Policy */
.refund-policy {
    background: var(--gray-50);
    padding: 2rem;
    border-radius: var(--border-radius-md);
    margin: 1.5rem 0;
}

.refund-policy h5 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

/* Schedule Info */
.schedule-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 1.5rem 0;
}

.schedule-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--border-radius-md);
}

.schedule-item i {
    color: var(--primary-purple);
    font-size: 1.5rem;
}

.schedule-item h5 {
    margin: 0 0 0.5rem 0;
    color: var(--gray-800);
}

.schedule-item p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

/* Delivery Process */
.delivery-process {
    display: grid;
    gap: 1rem;
    margin: 1.5rem 0;
}

.process-step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: var(--border-radius-md);
}

.step-number {
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    flex-shrink: 0;
}

.step-content h5 {
    margin: 0 0 0.5rem 0;
    color: var(--gray-800);
}

.step-content p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

/* Data Types */
.data-types {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin: 1.5rem 0;
}

.data-type {
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: var(--border-radius-md);
}

.data-type h5 {
    margin-bottom: 1rem;
    color: var(--gray-800);
}

.data-type ul {
    margin: 0;
    padding-left: 1rem;
}

/* Limitations */
.limitations {
    background: var(--gray-50);
    padding: 2rem;
    border-radius: var(--border-radius-md);
    margin: 1.5rem 0;
}

/* Prohibited Items */
.prohibited-list {
    display: grid;
    gap: 1rem;
    margin: 1.5rem 0;
}

.prohibited-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: var(--border-radius-md);
    border-left: 4px solid var(--danger-red);
}

.prohibited-item i {
    color: var(--danger-red);
    font-size: 1.5rem;
}

.prohibited-item h5 {
    margin: 0 0 0.5rem 0;
    color: var(--gray-800);
}

.prohibited-item p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

/* Claims Process */
.claims-process {
    display: grid;
    gap: 1rem;
    margin: 1.5rem 0;
}

.claim-step {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: var(--gray-50);
    border-radius: var(--border-radius-md);
}

.claim-icon {
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.claim-step h5 {
    margin: 0 0 0.5rem 0;
    color: var(--gray-800);
}

.claim-step p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--gray-600);
}

/* Contact Section */
.contact-section {
    padding: 4rem 0;
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
    text-align: center;
}

.contact-content {
    color: white;
}

.contact-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--gray-200);
}

.contact-content p {
    font-size: 1.125rem;
    margin-bottom: 3rem;
    opacity: 0.9;
    color: var(--gray-200);
}

.contact-methods {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.contact-method {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 2rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius-lg);
    backdrop-filter: blur(10px);
}

.contact-method i {
    font-size: 2rem;
    color: white;
}

.contact-method h4 {
    margin: 0 0 0.5rem 0;
    color: white;
}

.contact-method p {
    margin: 0;
    color: white;
    opacity: 0.9;
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .sidebar {
        order: 2;
    }

    .main-content {
        order: 1;
    }

    .sidebar-sticky {
        position: static;
    }
}

@media (max-width: 768px) {
    .content-article {
        padding: 2rem 1.5rem;
    }

    .content-article h2 {
        font-size: 1.5rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .nav-links {
        justify-content: flex-start;
    }

    .nav-link {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
    }

    .status-grid,
    .payment-methods,
    .data-types,
    .contact-methods {
        grid-template-columns: 1fr;
    }

    .process-step,
    .claim-step,
    .contact-method {
        flex-direction: column;
        text-align: center;
    }

    .prohibited-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>
