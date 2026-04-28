use serde::{Deserialize, Serialize};

/// Bundles call-site metadata so functions that operate on a variable
/// stay within the clippy `too_many_arguments` limit.
#[cfg(debug_assertions)]
#[doc(hidden)]
pub struct QuoContext<'a> {
    pub line: u32,
    pub file: &'a str,
    pub is_mutable: bool,
    pub is_expression: bool,
    pub package_name: &'a str,
    pub shared_grouping_hash: Option<String>,
}

#[derive(Serialize, Debug, PartialEq)]
pub struct QuoPayload {
    pub meta: QuoPayloadMeta,
    pub language: QuoPayloadLanguage,
}

#[derive(Serialize, Debug, PartialEq)]
pub struct QuoPayloadVariable {
    pub var_type: String,
    pub name: String,
    pub value: String,
    pub is_mutable: bool,
    pub is_constant: bool,
    pub is_expression: bool,

    #[serde(skip_serializing_if = "Option::is_none")]
    pub memory_address: Option<String>,

    #[serde(skip_serializing_if = "Option::is_none")]
    pub grouping_hash: Option<String>,
}

#[derive(Serialize, Debug, PartialEq)]
pub struct QuoPayloadMeta {
    pub id: u32,
    pub uid: String,
    pub origin: String,
    pub sender_origin: String,
    pub time_epoch_ms: i64,
    pub variable: QuoPayloadVariable,

    #[serde(skip_serializing_if = "Option::is_none")]
    pub stack_trace: Option<Vec<String>>,

    #[serde(skip_serializing_if = "Option::is_none")]
    pub thread_info: Option<String>,

    #[serde(skip_serializing_if = "Option::is_none")]
    pub runtime: Option<String>,

    #[serde(skip_serializing_if = "Option::is_none")]
    pub cpu_usage: Option<f32>,

    #[serde(skip_serializing_if = "Option::is_none")]
    pub memory_usage: Option<u64>,

    #[serde(skip_serializing_if = "Option::is_none")]
    pub caller_function: Option<String>,
}

#[derive(Serialize, Deserialize, Clone, Debug, PartialEq, Default)]
#[serde(rename_all = "lowercase")]
pub enum QuoPayloadLanguage {
    Rust,
    Php,
    Javascript,
    Typescript,
    Python,
    Ruby,
    Go,
    #[default]
    Unknown,
}
