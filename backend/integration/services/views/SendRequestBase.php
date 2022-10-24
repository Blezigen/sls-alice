<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mes="urn://artefacts-russiatourism-ru/services/message-exchange" xmlns:typ="urn://artefacts-russiatourism-ru/services/message-exchange/types" xmlns:bas="urn://artefacts-russiatourism-ru/services/message-exchange/types/basic">
    <soapenv:Header />
    <soapenv:Body>
        <mes:SendRequest>
            <typ:SendRequestRequest>
                <typ:SenderProvidedRequestData>
                    <typ:Sender>
                        <typ:Mnemonic><?= $mnemonic ?></typ:Mnemonic>
                        <typ:HumanReadableName><?= $humanReadableName ?></typ:HumanReadableName>
                    </typ:Sender>
                    <bas:MessagePrimaryContent>
                        <?= $content ?>
                    </bas:MessagePrimaryContent>
                </typ:SenderProvidedRequestData>
            </typ:SendRequestRequest>
        </mes:SendRequest>
    </soapenv:Body>
</soapenv:Envelope>