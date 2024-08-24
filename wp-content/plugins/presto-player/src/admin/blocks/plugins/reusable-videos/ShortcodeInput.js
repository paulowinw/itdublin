import { __ } from "@wordpress/i18n";
import { PanelBody } from "@wordpress/components";

async function copyTextToClipboard(e) {
  let textToCopy = document.getElementById("presto-shortcode-input").value;
  let btn = e.currentTarget;
  let buttonText = btn.textContent;
  e.currentTarget.textContent = __("Copied!", "presto-player");

  setTimeout(() => {
    btn.textContent = buttonText;
  }, 1500);

  if (navigator.clipboard && window.isSecureContext) {
    // navigator clipboard api method'
    return navigator.clipboard.writeText(textToCopy);
  } else {
    // text area method for older OR non secure URL pages.
    let textArea = document.createElement("textarea");
    textArea.value = textToCopy;
    // make the textarea out of viewport
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    return new Promise((res, rej) => {
      document.execCommand("copy") ? res() : rej();
      textArea.remove();
    });
  }
}

function InserterShortcodeInput() {
  return (
    <PanelBody
      title={__("Timestamp Shortcode", "presto-player")}
      initialOpen={false}
    >
      <div className={"block-editor-inserter__shortcode-input"}>
        <p>
          {__(
            "Add convenient links to skip the player to a specific timestamp.",
            "presto-player"
          )}
        </p>
        <input
          id="presto-shortcode-input"
          style={{ width: "100%", fontSize: "11px", marginBottom: "10px" }}
          type="text"
          readOnly
          value='[pptime time="1:00"]Optional Text[/pptime]'
        />
        <button
          type="button"
          className="components-button is-primary"
          onClick={copyTextToClipboard}
        >
          {__("Copy to clipboard", "presto-player")}
        </button>
      </div>
    </PanelBody>
  );
}

export default InserterShortcodeInput;
