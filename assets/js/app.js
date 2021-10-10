/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';
import "bootstrap";
import $ from "jquery";

import * as utils from "./utils";

function traverseFileTreePromise(item, path='') {
    return new Promise( resolve => {
        if (item.isFile) {
            item.file(file => {
                file.filepath = path + file.name; //save full path
                resolve(file);
            });
        } else if (item.isDirectory) {
            let dirReader = item.createReader();
            dirReader.readEntries(entries => {
                let entriesPromises = [];
                for (let entr of entries) {
                    entriesPromises.push(traverseFileTreePromise(entr, path + item.name + "/"));
                }
                resolve(Promise.all(entriesPromises));
            });
        }
    });
}

async function getFilesFromWebkitDataTransferItems(dataTransferItems) {
    let files = []
    for (const it of dataTransferItems) {
        const fileResult = await traverseFileTreePromise(it.webkitGetAsEntry());
        if (Array.isArray(fileResult)) {
            files = [...files, ...fileResult];
        } else {
            files.push(fileResult);
        }
    }
    return files;
}

$(() => {
    $("[data-method]").on("click", utils.ajaxLinkHandler);

    const $multiUploadArea = $("#multi-upload-area");
    if ($multiUploadArea != null) {
        $multiUploadArea[0].addEventListener('dragover', evt => evt.preventDefault())
        $multiUploadArea[0].addEventListener("drop", async evt => {
            evt.preventDefault();

            const items = evt.dataTransfer.items;
            const files = await getFilesFromWebkitDataTransferItems(items);
            for (const file of files.filter(f => (f.type.startsWith("image/")))) {
                const formData = new FormData();
                formData.append("image[imageFile][file]", file);
                const resp = await $.ajax({
                    type: "POST",
                    url: "/image/new?apiRequest=true",
                    data: formData,
                    processData: false,
                    contentType: false
                });
                console.log(resp);
            }
        }, false);
    }
});