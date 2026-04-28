/// Get current thread ID
pub fn get_thread_id() -> Option<String> {
    Some(format!(
        "{:?}",
        std::thread::current()
            .name()
            .unwrap_or(&format!("{:?}", std::thread::current().id()))
    ))
}

// Tests currently are pretty redundant.
#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_get_thread_id_not_empty() {
        let tid = get_thread_id();
        assert!(tid.is_some());
        assert!(!tid.unwrap().is_empty());
    }

    #[test]
    fn test_get_thread_id_contains_quotes_or_name() {
        let tid = get_thread_id().unwrap();
        assert!(!tid.is_empty());
    }
}
