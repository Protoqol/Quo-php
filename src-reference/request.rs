use crate::types::QuoPayload;

#[cfg(target_family = "wasm")]
use core::time::Duration;
#[cfg(target_family = "wasm")]
use ehttp::spawn_future;
#[cfg(target_family = "wasm")]
use ehttp::Request;
#[cfg(target_family = "wasm")]
use ehttp::{fetch_async, Credentials, Headers, Method, Mode};

#[cfg(not(target_family = "wasm"))]
use ureq::config::Config;

/// Make a request to Quo.
///
/// Should not be used directly.
#[doc(hidden)]
pub fn make_request(target: &str, payload: QuoPayload) {
    let url = target.to_string();

    #[cfg(target_family = "wasm")]
    {
        let mode = Mode::NoCors;
        let method = Method::POST;
        let timeout = Some(Duration::new(1, 0));
        let headers = Headers::new(&[("Content-Type", "application/json")]);

        let body = match serde_json_wasm::to_vec(&payload) {
            Ok(parsed_body) => parsed_body,
            Err(_) => {
                eprintln!("[QUO] Library error: Unparseable body. Could not convert to Vec<u8>.");
                return;
            }
        };

        let request = Request {
            mode,
            url,
            method,
            timeout,
            body,
            headers,
            credentials: Credentials::default(),
        };

        spawn_future(async move {
            match fetch_async(request).await {
                Ok(_) => {
                    // Assume Quo received the payload.
                }
                Err(err) => {
                    eprintln!("[QUO] error \"{}\" - is Quo running?", err);
                }
            };
        });
    }

    #[cfg(not(target_family = "wasm"))]
    {
        let config = Config::builder().user_agent("Quo-Rust").build();

        let agent = config.new_agent();

        match agent.post(url).send_json(payload) {
            Ok(_) => {}
            Err(ureq::Error::StatusCode(code)) => {
                eprintln!("[QUO] HTTP {} - is Quo running?", code)
            }
            Err(e) => {
                eprintln!("[QUO] error \"{}\" - is Quo running?", e)
            }
        };
    }
}

#[cfg(test)]
mod tests {
    // @TODO
}
