mod info;
pub mod macros;
mod request;
mod tests;
mod types;

use crate::info::hash::get_hash;
use crate::info::runtime::get_runtime;
use crate::info::stack_trace::get_stack_trace;
use crate::info::system_usage::get_system_usage;
use crate::info::thread::get_thread_id;
use crate::info::time::get_time;
use crate::request::make_request;
use crate::types::{QuoContext, QuoPayload, QuoPayloadLanguage, QuoPayloadMeta, QuoPayloadVariable};
pub use crate::types::QuoContext as __private_QuoContext;
use std::fmt::Debug;
use uuid::Uuid;

/// This fn creates a QuoPayload. You might or might not question why this is a separate function: for testing.
///
/// # Example
///
/// let mut big_number: i128;
///
/// big_number = 170141183460469231731687303715884105727;
///
/// quo_create_payload(&big_number, "big_number", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "quo-rust", shared_grouping_hash: None });
///
#[cfg(debug_assertions)]
#[doc(hidden)]
fn quo_create_payload<T: Debug>(value: T, name: &str, ctx: QuoContext<'_>) -> QuoPayload {
    let id = 0; // @TODO Pretty useless currently.
    let var_type_raw = std::any::type_name_of_val(&value).to_string();
    let var_type = var_type_raw
        .strip_prefix('&')
        .unwrap_or(&var_type_raw)
        .to_string();

    let value_str = format!("{:?}", value);

    let uid: String = Uuid::new_v4().to_string();
    let time_epoch_ms = get_time();
    let memory_address = Some(format!("{:p}", &value as *const T));
    let grouping_hash = ctx.shared_grouping_hash.or_else(|| get_hash(&var_type_raw, name, ctx.package_name));
    let (stack_trace, caller_function) = get_stack_trace();
    let thread_info = get_thread_id();
    let (cpu_usage, memory_usage) = get_system_usage();
    let runtime = get_runtime();

    QuoPayload {
        language: QuoPayloadLanguage::Rust,
        meta: QuoPayloadMeta {
            origin: ctx.package_name.to_string(),
            sender_origin: format!("{}:{}", ctx.file, ctx.line),
            variable: QuoPayloadVariable {
                var_type: var_type.clone(),
                name: name.to_string(),
                value: value_str,
                is_constant: name == name.to_uppercase(),
                is_mutable: ctx.is_mutable || var_type_raw.contains("&mut "),
                is_expression: ctx.is_expression,
                memory_address,
                grouping_hash,
            },
            id,
            uid,
            time_epoch_ms,
            stack_trace,
            thread_info,
            runtime,
            cpu_usage,
            memory_usage,
            caller_function,
        },
    }
}

/// This fn sends the provided variable to Quo.
///
/// # Example
///
/// let mut big_number: i128;
///
/// big_number = 170141183460469231731687303715884105727;
///
/// quo(&big_number, "big_number", QuoContext { line: line!(), file: file!(), is_mutable: false, is_expression: false, package_name: "quo-rust", shared_grouping_hash: None });
///
#[cfg(debug_assertions)]
#[doc(hidden)]
fn quo<T: Debug>(value: T, name: &str, ctx: QuoContext<'_>) {
    #[cfg(debug_assertions)]
    {
        let env_host = option_env!("QUO_HOST").unwrap_or("http://127.0.0.1");
        let env_port = option_env!("QUO_PORT").unwrap_or("7312");

        let body = quo_create_payload(value, name, ctx);
        let quo_server_address = format!("{}:{}/payload", env_host, env_port);

        make_request(&quo_server_address, body);
    }
}

/// Use the `quo!()` macro and not this fn directly.
/// Returns a hash that is unique per call-site invocation: the nanosecond
/// timestamp is mixed in so two separate `quo!(…)` calls — even with
/// identical arguments — always produce different grouping hashes.
#[cfg(debug_assertions)]
#[doc(hidden)]
pub fn __private_quo_grouping_hash(args_key: &str, package_name: &str) -> Option<String> {
    let call_uid = Uuid::new_v4().to_string();
    get_hash("grouped", &format!("{args_key}:{call_uid}"), package_name)
}

/// Use the `quo!()` macro and not this fn directly.
#[cfg(debug_assertions)]
#[doc(hidden)]
pub fn __private_quo<T: Debug>(value: T, name: &str, ctx: QuoContext<'_>) {
    quo(value, name, ctx)
}
