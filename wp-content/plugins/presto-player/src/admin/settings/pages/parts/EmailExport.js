const { __ } = wp.i18n;
const {
  Button,
  Panel,
  PanelBody,
  Flex,
  FlexBlock,
  Notice,
  Spinner,
} = wp.components;
const { useState, useEffect } = wp.element;

export default () => {
  const [step, setStep] = useState(0);
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState("");
  const [url, setURL] = useState(0);

  const exportEmails = async () => {
    setError("");
    try {
      const {
        percentage,
        step: currentStep,
        url: fetchedURL,
      } = await wp.apiFetch({
        path: "/presto-player/v1/email/export",
        method: "post",
        data: {
          step,
        },
      });

      setStep(currentStep);
      setProgress(percentage);
      setURL(fetchedURL);
    } catch (e) {
      setProgress(0);
      setError(e?.message || __("Something went wrong", "presto-player"));
    }
  };

  useEffect(() => {
    if (step && step != "done") {
      exportEmails();
    }
  }, [step]);

  useEffect(() => {
    if (url) {
      window.open(url);
    }
  }, [url]);

  return (
    <Panel>
      <PanelBody
        title={
          <Flex>
            <FlexBlock>{__("Other", "presto-player")}</FlexBlock>
          </Flex>
        }
        initialOpen={false}
      >
        <h2>{__("Manual Export", "presto-player")}</h2>
        <p>
          {__(
            "Using a service not listed here? You can export contacts and manually upload them to a service.",
            "presto-player"
          )}
        </p>
        {error && (
          <Notice
            className="presto-notice"
            status="error"
            onRemove={() => setError("")}
          >
            {error}
          </Notice>
        )}
        <Flex align="center" justify="flex-start">
          <Button
            isPrimary
            onClick={(e) => {
              setStep(1);
              setProgress(1);
              e.preventDefault();
            }}
          >
            {__("Download CSV File", "presto-player")}
          </Button>
          {!!progress && (
            <div
              style={{
                display: "flex",
                alignItems: "center",
              }}
            >
              <Spinner style={{ marginTop: 0 }} />
              <span>Exporting... {progress}% Complete</span>
            </div>
          )}
        </Flex>
      </PanelBody>
    </Panel>
  );
};
