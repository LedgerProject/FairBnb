<div class="tabs">
    {if $product_certificate=="null"}
        <ul class="nav nav-tabs">
            <div class="row text-center align-middle nav-link active">
                <div class="col-sm-6 font-weight-bold">
                    <a class="">Certificate</a>
                </div>
                <div class="col-sm-6">
                    <span class="itc_float_right font-weight-bold itc_error_verify"><i class="material-icons"></i>Not Available</span>
                </div>

            </div>
        </ul>
    {else}
        <ul class="nav nav-tabs">
            <div class="row text-center align-middle nav-link active">
                <div class="row">
                    <div class="col-sm-6 font-weight-bold">
                        <a class="">Certificate</a>
                    </div>
                    <div class="col-sm-6 font-weight-bold">
                        <a class="">Local Ambassador: {$id_employee}</a>
                    </div>
                </div>
            </div>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade in active">
                <div class="row">

                    {*//SIGNED CONTRACT*}
                    <div class="col-sm-12">
                        <div class="card">
                            <h6 class="card-header itc_padding">
                                <div class="row">
                                    <div class="col-sm-6 itc_padding_11px"><strong>Signed Contract</strong></div>
                                    <div class="col-sm-6">
                                        <button class="btn dropdown-toggle itc_float_right" type="button"
                                                data-toggle="collapse"
                                                data-target="#itcCollapseContract" aria-expanded="false"
                                                aria-controls="itcCollapseContract">
                                        </button>
                                        <button class="btn itc_float_right type="button" onclick="copyToClipboard('#product_certificate')"><i
                                                        class="material-icons" id="product_certificate_icon">content_copy</i></button>
                                    </div>
                                </div>
                            </h6>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="collapse" id="itcCollapseContract">
                            <div class="card-text">
                                <div class="form-group">
                                    <div class="itc_textarea_div">
                                        <textarea id="product_certificate" class="itc_textarea" readonly>{$product_certificate}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {*//ECDH*}

                    {*<div class="col-sm-12">
                        <div class="card">
                            <h6 class="card-header itc_padding">
                                <div class="row">
                                    <div class="col-sm-6 itc_padding_11px"><strong>ECDH</strong></div>
                                    <div class="col-sm-6">
                                        <button class="btn dropdown-toggle itc_float_right" type="button"
                                                data-toggle="collapse"
                                                data-target="#itcCollapseEcdh" aria-expanded="false"
                                                aria-controls="itcCollapseEcdh">
                                        </button>
                                        <button class="btn itc_float_right" type="button" onclick="copyToClipboard('#ecdh_textarea')"><i
                                                    class="material-icons" id="ecdh_textarea_icon">content_copy</i></button>
                                    </div>
                                </div>
                            </h6>
                        </div>
                    </div>*}

                    <div class="col-sm-12">
                        <div class="collapse" id="itcCollapseEcdh">
                            <div class="card-text">
                                <div class="form-group">
                                    <div class="itc_textarea_div">
                                        <textarea id="ecdh_textarea" class="itc_textarea" readonly>Scenario 'ecdh': Bob verifies the signature from Alice
Given I have a 'public key' from '{$id_employee}'
Given I have a 'string dictionary' named 'productInfo'
Given I have a 'signature' named 'productInfo.signature'
When I verify the 'productInfo' has a signature in 'productInfo.signature' by '{$id_employee}'
Then print 'productInfo'</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane fade in active">
                <div class="row">
                    <div class="col-sm-11">
                        <strong>Public Key</strong>
                    </div>
                    <div class="col-sm-1">
                        <button class="btn itc_float_right" type="button" onclick="copyToClipboard('#public_key_text_area')"><i
                                    class="material-icons" id="public_key_text_area_icon">content_copy</i></button>
                    </div>
                    <div class="col-sm-12 itc_margin_top">
                        <textarea id="public_key_text_area" class="itc_textarea {*itc_textarea_div_public_key*}" readonly>{*{
    {
        "$id_employee}": { "public_key": "{$public_key}"
    }
}*}{$public_key}</textarea>
                    </div>
                    <div class="col-sm-11">
                        <strong>Signature</strong>
                    </div>
                    <div class="col-sm-1">
                        <button class="btn itc_float_right" type="button" onclick="copyToClipboard('#signature_text_area')"><i
                                    class="material-icons" id="signature_text_area_icon">content_copy</i></button>
                    </div>
                    <div class="col-sm-12 itc_margin_top">
                        <div class="itc_textarea_div_signature">
                            <textarea id="signature_text_area" class="itc_textarea" readonly>{$signature}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane fade in active">
                <div class="row">
                    <div class="col-sm-6">
                        <button id="verify_sign" type="button" class="btn btn-primary" onclick="verifySign()"><i
                                    class="material-icons">edit</i>Verify Sign
                        </button>
                    </div>
                    <div class="col-sm-6">
                        <strong class="" id="itc_message_verify"></strong>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</div>


{if $product_certificate!="null"}
    <script type="module">
        var itc_id_employee = "{$id_employee}";
        var itc_keys = "{ \"{$id_employee}\": { \"public_key\": \"{$public_key}\" } }";


        var itc_data = "{$product_certificate_js}".replace(/&quot;/g, '"');

        {literal}
        import {zencode_exec} from "https://jspm.dev/zenroom";

        const conf = "memmanager=lw";

        window.verifySign = () => {
            const keys = itc_keys;
            const data = itc_data;
            const messageText = $("#itc_message_verify");

            const contract = `Scenario 'ecdh': Bob verifies the signature from Alice
                            Given I have a 'public key' from '` + itc_id_employee + `'
                            Given I have a 'string dictionary' named 'productInfo'
                            Given I have a 'signature' named 'productInfo.signature'
                            When I verify the 'productInfo' has a signature in 'productInfo.signature' by '` + itc_id_employee + `'
                            Then print 'productInfo'`;
            zencode_exec(contract, {data, keys, conf}).then(({result}) => {
                messageText.html("Contract Verified");
                messageText.addClass("itc_success_verify");
            }, reason => {
                messageText.html("Contract NOT Verified");
                messageText.addClass("itc_error_verify");
            });
        }
        {/literal}


    </script>
{/if}
