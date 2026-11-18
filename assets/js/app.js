/**
 * Horizonte Viagens — interações da interface.
 * JavaScript puro, sem dependências externas.
 */
(function () {
    'use strict';

    /* ---------------------------------------------------------------------
     * Utilidades
     * ------------------------------------------------------------------- */

    const selecionar = (seletor, contexto = document) => contexto.querySelector(seletor);
    const selecionarTodos = (seletor, contexto = document) => Array.from(contexto.querySelectorAll(seletor));

    const somenteDigitos = (valor) => String(valor ?? '').replace(/\D+/g, '');

    const formatarMoeda = (valor) => 'R$ ' + Number(valor || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const formatarNumero = (valor) => Number(valor || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    /**
     * Substitui o texto de um elemento mantendo o ícone SVG que já existe nele.
     */
    const definirTextoComIcone = (elemento, texto) => {
        if (!elemento) {
            return;
        }

        const icone = elemento.querySelector('svg');
        elemento.textContent = '';

        if (icone) {
            elemento.appendChild(icone.cloneNode(true));
            elemento.appendChild(document.createTextNode(' '));
        }

        elemento.appendChild(document.createTextNode(texto));
    };

    /* ---------------------------------------------------------------------
     * Menu lateral (mobile)
     * ------------------------------------------------------------------- */

    const sidebar = selecionar('.sidebar');
    const overlay = selecionar('.sidebar-overlay');

    const fecharMenu = () => {
        sidebar?.classList.remove('aberto');
        overlay?.classList.remove('is-visivel');
        document.body.classList.remove('sem-rolagem');
    };

    selecionarTodos('[data-abrir-menu]').forEach((botao) => {
        botao.addEventListener('click', () => {
            sidebar?.classList.add('aberto');
            overlay?.classList.add('is-visivel');
            document.body.classList.add('sem-rolagem');
        });
    });

    selecionarTodos('[data-fechar-menu]').forEach((elemento) => {
        elemento.addEventListener('click', fecharMenu);
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            fecharMenu();
        }
    });

    /* ---------------------------------------------------------------------
     * Mensagens / alertas
     * ------------------------------------------------------------------- */

    const fecharAlerta = (alerta) => {
        if (!alerta) {
            return;
        }

        alerta.style.transition = 'opacity .2s ease, transform .2s ease';
        alerta.style.opacity = '0';
        alerta.style.transform = 'translateY(-4px)';

        window.setTimeout(() => alerta.remove(), 220);
    };

    selecionarTodos('[data-fechar-alerta]').forEach((botao) => {
        botao.addEventListener('click', () => fecharAlerta(botao.closest('.alerta, .flash')));
    });

    // Fecha automaticamente mensagens de sucesso e informação após alguns segundos.
    selecionarTodos('.conteudo .flash--sucesso, .conteudo .flash--info').forEach((alerta) => {
        window.setTimeout(() => fecharAlerta(alerta), 9000);
    });

    /* ---------------------------------------------------------------------
     * Confirmação de ações destrutivas
     * ------------------------------------------------------------------- */

    selecionarTodos('form[data-confirmar]').forEach((formulario) => {
        formulario.addEventListener('submit', (evento) => {
            const mensagem = formulario.getAttribute('data-confirmar') || 'Confirmar esta ação?';

            if (!window.confirm(mensagem)) {
                evento.preventDefault();
            }
        });
    });

    /* ---------------------------------------------------------------------
     * Máscaras de formulário
     * ------------------------------------------------------------------- */

    const mascararCpf = (valor) => {
        const digitos = somenteDigitos(valor).slice(0, 11);
        return digitos
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d{1,2})$/, '.$1-$2');
    };

    const mascararTelefone = (valor) => {
        const digitos = somenteDigitos(valor).slice(0, 11);

        if (digitos.length === 0) {
            return '';
        }
        if (digitos.length <= 2) {
            return '(' + digitos;
        }
        if (digitos.length <= 6) {
            return '(' + digitos.slice(0, 2) + ') ' + digitos.slice(2);
        }
        if (digitos.length <= 10) {
            return '(' + digitos.slice(0, 2) + ') ' + digitos.slice(2, 6) + '-' + digitos.slice(6);
        }

        return '(' + digitos.slice(0, 2) + ') ' + digitos.slice(2, 7) + '-' + digitos.slice(7);
    };

    const mascararCep = (valor) => {
        const digitos = somenteDigitos(valor).slice(0, 8);
        return digitos.length > 5 ? digitos.slice(0, 5) + '-' + digitos.slice(5) : digitos;
    };

    const mascararMoeda = (valor) => {
        const limpo = String(valor ?? '').replace(/[^\d,]/g, '');
        const partes = limpo.split(',');
        const inteiro = (partes[0] || '').replace(/^0+(?=\d)/, '');
        const decimais = partes.length > 1 ? partes.slice(1).join('').slice(0, 2) : null;
        const inteiroFormatado = (inteiro || (decimais !== null ? '0' : '')).replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        if (decimais === null) {
            return inteiroFormatado;
        }

        return inteiroFormatado + ',' + decimais;
    };

    const normalizarMoeda = (campo) => {
        const valor = String(campo.value ?? '').trim();

        if (valor === '') {
            return;
        }

        const numero = Number(valor.replace(/\./g, '').replace(',', '.'));

        campo.value = Number.isFinite(numero) ? formatarNumero(numero) : '';
    };

    const aplicarMascara = (campo) => {
        const tipo = campo.getAttribute('data-mascara');

        switch (tipo) {
            case 'cpf':
                campo.value = mascararCpf(campo.value);
                break;
            case 'telefone':
                campo.value = mascararTelefone(campo.value);
                break;
            case 'cep':
                campo.value = mascararCep(campo.value);
                break;
            case 'moeda':
                campo.value = mascararMoeda(campo.value);
                break;
            default:
                break;
        }
    };

    selecionarTodos('[data-mascara]').forEach((campo) => {
        campo.addEventListener('input', () => aplicarMascara(campo));

        if (campo.getAttribute('data-mascara') === 'moeda') {
            campo.addEventListener('blur', () => normalizarMoeda(campo));
        }
    });

    // Garante que valores monetários sejam enviados com centavos (ex.: 2.500,00).
    selecionarTodos('form').forEach((formulario) => {
        formulario.addEventListener('submit', () => {
            selecionarTodos('[data-mascara="moeda"]', formulario).forEach((campo) => {
                if (String(campo.value).trim() !== '') {
                    normalizarMoeda(campo);
                }
            });
        });
    });

    /* ---------------------------------------------------------------------
     * Mostrar / ocultar senha
     * ------------------------------------------------------------------- */

    selecionarTodos('[data-mostrar-senha]').forEach((botao) => {
        botao.addEventListener('click', () => {
            const campo = document.getElementById(botao.getAttribute('data-mostrar-senha') || '');

            if (!campo) {
                return;
            }

            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            botao.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
            botao.classList.toggle('ativo', mostrar);
        });
    });

    // Alternância de senha usada nos formulários internos (sem id explícito).
    selecionarTodos('[data-alternar-senha]').forEach((botao) => {
        botao.addEventListener('click', () => {
            const grupo = botao.closest('.grupo-senha') || botao.parentElement;
            const campo = grupo ? grupo.querySelector('input') : null;

            if (!campo) {
                return;
            }

            const mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            botao.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
        });
    });

    /* ---------------------------------------------------------------------
     * Campos em maiúsculas (UF, códigos)
     * ------------------------------------------------------------------- */

    selecionarTodos('[data-maiuscula]').forEach((campo) => {
        campo.addEventListener('input', () => {
            const posicao = campo.selectionStart;
            campo.value = campo.value.toUpperCase();

            try {
                campo.setSelectionRange(posicao, posicao);
            } catch (erro) {
                // Alguns tipos de campo não permitem seleção programática.
            }
        });
    });

    /* ---------------------------------------------------------------------
     * Impressão
     * ------------------------------------------------------------------- */

    selecionarTodos('[data-imprimir]').forEach((botao) => {
        botao.addEventListener('click', () => window.print());
    });

    /* ---------------------------------------------------------------------
     * Formulário de pacote: ida x volta
     * ------------------------------------------------------------------- */

    const campoIda = selecionar('[data-data-ida]');
    const campoVolta = selecionar('[data-data-volta]');

    if (campoIda && campoVolta) {
        const ajustarPeriodo = () => {
            if (!campoIda.value) {
                campoVolta.removeAttribute('min');
                return;
            }

            campoVolta.min = campoIda.value;

            if (campoVolta.value && campoVolta.value < campoIda.value) {
                campoVolta.value = campoIda.value;
            }
        };

        campoIda.addEventListener('change', ajustarPeriodo);
        ajustarPeriodo();
    }

    /* ---------------------------------------------------------------------
     * Formulário de parcela: preenche o saldo da reserva selecionada
     * ------------------------------------------------------------------- */

    const selectReserva = selecionar('[data-select-reserva]');

    if (selectReserva) {
        const campoValor = document.getElementById('valor');

        const preencherSaldo = () => {
            const opcao = selectReserva.selectedOptions[0];

            if (!opcao || !opcao.value || !campoValor) {
                return;
            }

            const saldo = Number(opcao.getAttribute('data-saldo') || 0);

            if (saldo > 0) {
                campoValor.value = formatarNumero(saldo);
            }
        };

        selectReserva.addEventListener('change', preencherSaldo);
    }

    /* ---------------------------------------------------------------------
     * Formulário de reserva: resumo, total e disponibilidade
     * ------------------------------------------------------------------- */

    const formularioReserva = selecionar('[data-form-reserva]');

    if (formularioReserva) {
        const cartao = selecionar('[data-resumo-reserva]', formularioReserva) || selecionar('[data-resumo-reserva]');
        const selectPacote = selecionar('[data-pacote]', formularioReserva);
        const campoQtd = selecionar('[data-qtd]', formularioReserva);
        const selectParcelas = selecionar('[data-parcelas]', formularioReserva);
        const botaoEnviar = formularioReserva.querySelector('button[type="submit"]');

        const elementos = cartao ? {
            nome: selecionar('[data-resumo-nome]', cartao),
            destino: selecionar('[data-resumo-destino]', cartao),
            periodo: selecionar('[data-resumo-periodo]', cartao),
            preco: selecionar('[data-resumo-preco]', cartao),
            qtd: selecionar('[data-resumo-qtd]', cartao),
            total: selecionar('[data-resumo-total]', cartao),
            parcela: selecionar('[data-resumo-parcela]', cartao),
            vagas: selecionar('[data-resumo-vagas]', cartao),
            saldo: selecionar('[data-resumo-saldo]', cartao),
        } : {};

        const pagoNaEdicao = Number(cartao?.getAttribute('data-pago') || 0);

        const atualizarResumo = () => {
            if (!cartao) {
                return;
            }

            const opcao = selectPacote?.selectedOptions?.[0] || null;
            const pacoteSelecionado = Boolean(opcao && opcao.value);

            let preco = Number(cartao.getAttribute('data-preco') || 0);
            let restantes = null;
            let quantidade = Math.max(0, parseInt(campoQtd?.value || '0', 10) || 0);

            if (selectPacote) {
                if (!pacoteSelecionado) {
                    preco = 0;
                    definirTextoComIcone(elementos.nome, 'Selecione um pacote');
                    definirTextoComIcone(elementos.destino, 'Destino não selecionado');
                    definirTextoComIcone(elementos.periodo, 'Datas não definidas');

                    if (elementos.vagas) {
                        elementos.vagas.classList.remove('disponivel', 'esgotado');
                        definirTextoComIcone(elementos.vagas, 'Selecione um pacote para ver as vagas.');
                    }
                } else {
                    preco = Number(opcao.getAttribute('data-preco') || 0);
                    restantes = Number(opcao.getAttribute('data-restantes') || 0);

                    definirTextoComIcone(elementos.nome, opcao.getAttribute('data-nome') || 'Pacote');
                    definirTextoComIcone(elementos.destino, opcao.getAttribute('data-destino') || 'Destino não informado');
                    definirTextoComIcone(
                        elementos.periodo,
                        (opcao.getAttribute('data-ida') || '—') + ' a ' + (opcao.getAttribute('data-volta') || '—')
                    );

                    if (campoQtd) {
                        campoQtd.max = String(Math.max(1, restantes));

                        if (restantes > 0 && quantidade > restantes) {
                            quantidade = restantes;
                            campoQtd.value = String(restantes);
                        }
                    }

                    if (elementos.vagas) {
                        elementos.vagas.classList.remove('disponivel', 'esgotado');

                        if (restantes <= 0) {
                            elementos.vagas.classList.add('esgotado');
                            definirTextoComIcone(elementos.vagas, 'Pacote esgotado. Selecione outro pacote.');
                        } else if (quantidade > restantes) {
                            elementos.vagas.classList.add('esgotado');
                            definirTextoComIcone(elementos.vagas, 'Restam apenas ' + restantes + ' vaga(s) neste pacote.');
                        } else {
                            elementos.vagas.classList.add('disponivel');
                            definirTextoComIcone(
                                elementos.vagas,
                                restantes + ' vaga(s) disponível(is) · ' + quantidade + ' selecionada(s)'
                            );
                        }
                    }

                    if (botaoEnviar) {
                        botaoEnviar.disabled = restantes <= 0;
                    }
                }
            }

            const total = Math.max(0, preco * quantidade);

            if (elementos.preco) {
                elementos.preco.textContent = formatarMoeda(preco);
            }
            if (elementos.qtd) {
                elementos.qtd.textContent = String(quantidade);
            }
            if (elementos.total) {
                elementos.total.textContent = formatarMoeda(total);
            }

            if (selectParcelas && elementos.parcela) {
                const parcelas = Math.max(1, parseInt(selectParcelas.value || '1', 10) || 1);

                if (total > 0) {
                    elementos.parcela.textContent = parcelas + 'x de ' + formatarMoeda(total / parcelas);
                } else {
                    elementos.parcela.textContent = '—';
                }
            }

            if (elementos.saldo) {
                const saldo = Math.max(0, total - pagoNaEdicao);
                elementos.saldo.textContent = formatarMoeda(saldo);
            }
        };

        selectPacote?.addEventListener('change', atualizarResumo);
        campoQtd?.addEventListener('input', atualizarResumo);
        campoQtd?.addEventListener('change', atualizarResumo);
        selectParcelas?.addEventListener('change', atualizarResumo);

        atualizarResumo();
    }
})();
