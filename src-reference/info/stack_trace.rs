/// Get stack trace
pub fn get_stack_trace() -> (Option<Vec<String>>, Option<String>) {
    #[cfg(feature = "stack-trace")]
    {
        let mut frames = Vec::new();
        let mut caller = None;

        backtrace::trace(|frame| {
            backtrace::resolve_frame(frame, |symbol| {
                if let Some(name) = symbol.name() {
                    let name_str = name.to_string();

                    if !name_str.contains("quo_rust") && !name_str.contains("backtrace") {
                        if caller.is_none() {
                            caller = Some(name_str.clone());
                        }

                        frames.push(name_str);
                    }
                }
            });
            true
        });
        (Some(frames), caller)
    }

    #[cfg(not(feature = "stack-trace"))]
    (None, None)
}

// Tests currently are pretty redundant.
#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_stack_trace_none_without_feature() {
        #[cfg(not(feature = "stack-trace"))]
        {
            let (bt, caller) = get_stack_trace();
            assert!(bt.is_none());
            assert!(caller.is_none());
        }
    }

    #[test]
    #[cfg(feature = "stack-trace")]
    fn test_stack_trace_captured() {
        let (bt, caller) = get_stack_trace();
        assert!(bt.is_some());
        assert!(caller.is_some());
        assert!(!bt.unwrap().is_empty());
    }
}
